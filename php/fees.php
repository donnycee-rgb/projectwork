<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

// ── Helpers ────────────────────────────────────────────────────────────────

function jsonError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function requireAuth(): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
        jsonError('Unauthorized.', 401);
    }
}

function requireAdmin(): void
{
    requireAuth();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        jsonError('Access denied.', 403);
    }
}

function isAdmin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

function readJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if ($raw) {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
    }
    return $_POST;
}

function fetchCostPerCredit(PDO $pdo): float
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'cost_per_credit' LIMIT 1");
    $stmt->execute();
    $value = $stmt->fetchColumn();
    if ($value === false) {
        return 5000.0;
    }
    $num = (float) $value;
    return $num > 0 ? $num : 5000.0;
}

function fetchStudentFeeRow(PDO $pdo, int $studentId, float $costPerCredit): ?array
{
    $stmt = $pdo->prepare(
        "SELECT u.id, u.first_name, u.last_name, u.email, u.student_id, u.fees_paid,
                COUNT(r.id)                  AS enrolled_count,
                COALESCE(SUM(c.credits), 0)  AS enrolled_credits
         FROM users u
         LEFT JOIN registrations r ON r.user_id = u.id
         LEFT JOIN courses c       ON c.id = r.course_id
         WHERE u.id = ?
         GROUP BY u.id, u.first_name, u.last_name, u.email, u.student_id, u.fees_paid
         LIMIT 1"
    );
    $stmt->execute([$studentId]);
    $row = $stmt->fetch();

    if (!$row) {
        return null;
    }

    $feesPaid         = (float) $row['fees_paid'];
    $creditsUsed      = (int)   $row['enrolled_credits'];
    $creditsAvailable = $costPerCredit > 0 ? (int) floor($feesPaid / $costPerCredit) : 0;
    $creditsRemaining = $creditsAvailable - $creditsUsed;

    return [
        'id'               => (int) $row['id'],
        'name'             => trim($row['first_name'] . ' ' . $row['last_name']),
        'email'            => $row['email'],
        'student_id'       => $row['student_id'],
        'fees_paid'        => number_format($feesPaid, 2, '.', ''),
        'cost_per_credit'  => number_format($costPerCredit, 2, '.', ''),
        'credits_available'=> $creditsAvailable,
        'credits_used'     => $creditsUsed,
        'credits_remaining'=> $creditsRemaining,
        'enrolled_count'   => (int) $row['enrolled_count'],
        'enrolled_credits' => $creditsUsed,
    ];
}

// ── Routing ────────────────────────────────────────────────────────────────

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// ── GET ────────────────────────────────────────────────────────────────────
if ($method === 'GET') {
    requireAuth();

    // Students fetch their own fee data
    if ($action === 'student') {
        $requestedId = (int) ($_GET['id'] ?? 0);
        if ($requestedId < 1) {
            jsonError('Student ID is required.');
        }

        // Students may only view their own record
        if (!isAdmin() && $requestedId !== (int) $_SESSION['user_id']) {
            jsonError('Access denied.', 403);
        }

        $costPerCredit = fetchCostPerCredit($pdo);
        $data          = fetchStudentFeeRow($pdo, $requestedId, $costPerCredit);

        if (!$data) {
            jsonError('Student not found.', 404);
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // Everything below is admin-only
    requireAdmin();
    $costPerCredit = fetchCostPerCredit($pdo);

    if ($action === 'all') {
        $stmt = $pdo->query(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.student_id, u.fees_paid,
                    COUNT(r.id)                 AS enrolled_count,
                    COALESCE(SUM(c.credits), 0) AS enrolled_credits
             FROM users u
             LEFT JOIN registrations r ON r.user_id = u.id
             LEFT JOIN courses c       ON c.id = r.course_id
             WHERE u.role = 'student'
             GROUP BY u.id, u.first_name, u.last_name, u.email, u.student_id, u.fees_paid
             ORDER BY u.last_name, u.first_name"
        );

        $students = array_map(static function (array $row) use ($costPerCredit): array {
            $feesPaid         = (float) $row['fees_paid'];
            $creditsUsed      = (int)   $row['enrolled_credits'];
            $creditsAvailable = $costPerCredit > 0 ? (int) floor($feesPaid / $costPerCredit) : 0;
            $creditsRemaining = $creditsAvailable - $creditsUsed;

            return [
                'id'                => (int) $row['id'],
                'name'              => trim($row['first_name'] . ' ' . $row['last_name']),
                'email'             => $row['email'],
                'student_id'        => $row['student_id'],
                'fees_paid'         => number_format($feesPaid, 2, '.', ''),
                'credits_available' => $creditsAvailable,
                'credits_used'      => $creditsUsed,
                'credits_remaining' => $creditsRemaining,
                'enrolled_count'    => (int) $row['enrolled_count'],
                'enrolled_credits'  => $creditsUsed,
            ];
        }, $stmt->fetchAll());

        echo json_encode([
            'success'        => true,
            'cost_per_credit'=> number_format($costPerCredit, 2, '.', ''),
            'students'       => $students,
        ]);
        exit;
    }

    if ($action === 'settings') {
        echo json_encode([
            'success'        => true,
            'cost_per_credit'=> number_format($costPerCredit, 2, '.', ''),
        ]);
        exit;
    }

    jsonError('Unknown action.');
}

// ── POST ───────────────────────────────────────────────────────────────────
if ($method === 'POST') {
    requireAdmin();

    $data       = readJsonBody();
    $postAction = $data['action'] ?? $action;

    if ($postAction === 'update') {
        $studentId = (int)   ($data['student_id'] ?? 0);
        $amount    = isset($data['amount']) ? (float) $data['amount'] : -1;

        if ($studentId < 1) {
            jsonError('Student ID is required.');
        }
        if ($amount < 0) {
            jsonError('Amount must be zero or a positive number.');
        }

        $exists = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' LIMIT 1");
        $exists->execute([$studentId]);
        if (!$exists->fetch()) {
            jsonError('Student not found.', 404);
        }

        $pdo->prepare('UPDATE users SET fees_paid = ? WHERE id = ?')
            ->execute([number_format($amount, 2, '.', ''), $studentId]);

        $costPerCredit = fetchCostPerCredit($pdo);
        $student       = fetchStudentFeeRow($pdo, $studentId, $costPerCredit);

        echo json_encode([
            'success' => true,
            'message' => 'Fees updated successfully.',
            'student' => $student,
        ]);
        exit;
    }

    if ($postAction === 'settings') {
        $costPerCredit = isset($data['cost_per_credit']) ? (float) $data['cost_per_credit'] : 0;

        if ($costPerCredit <= 0) {
            jsonError('Cost per credit must be a positive number.');
        }

        $pdo->prepare(
            "INSERT INTO settings (setting_key, setting_value)
             VALUES ('cost_per_credit', ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        )->execute([number_format($costPerCredit, 2, '.', '')]);

        echo json_encode([
            'success'        => true,
            'message'        => 'Cost per credit updated.',
            'cost_per_credit'=> number_format($costPerCredit, 2, '.', ''),
        ]);
        exit;
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed.', 405);