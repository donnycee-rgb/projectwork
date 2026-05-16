<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

function jsonError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function requireAdmin(): void
{
    if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
        jsonError('Access denied.', 403);
    }
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

requireAdmin();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    if ($action !== 'all') {
        jsonError('Unknown action.');
    }

    $stmt = $pdo->query(
        "SELECT a.id, a.first_name, a.last_name, a.email, a.created_at, a.created_by,
                CONCAT(COALESCE(c.first_name, ''), ' ', COALESCE(c.last_name, '')) AS created_by_name
         FROM users a
         LEFT JOIN users c ON c.id = a.created_by
         WHERE a.role = 'admin'
         ORDER BY a.created_at ASC, a.id ASC"
    );
    $rows = $stmt->fetchAll();

    $admins = array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'name' => trim($row['first_name'] . ' ' . $row['last_name']),
            'email' => $row['email'],
            'created_at' => $row['created_at'],
            'created_by' => $row['created_by_name'] !== '' ? trim($row['created_by_name']) : 'System',
        ];
    }, $rows);

    echo json_encode(['success' => true, 'admins' => $admins]);
    exit;
}

if ($method === 'POST') {
    $data = readJsonBody();
    $postAction = $data['action'] ?? $action;

    if ($postAction === 'create') {
        $firstName = trim($data['first_name'] ?? '');
        $lastName = trim($data['last_name'] ?? '');
        $email = strtolower(trim($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($firstName === '' || $lastName === '' || $email === '' || $password === '') {
            jsonError('All fields are required.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonError('A valid email is required.');
        }
        if (strlen($password) < 8) {
            jsonError('Password must be at least 8 characters.');
        }

        $dup = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $dup->execute([$email]);
        if ($dup->fetch()) {
            jsonError('An account with this email already exists.');
        }

        $placeholder = 'ADM-' . date('YmdHis') . '-' . random_int(100, 999);
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $creatorId = (int) $_SESSION['user_id'];

        $insert = $pdo->prepare(
            "INSERT INTO users (
                first_name, last_name, email, student_id, password_hash, role,
                must_change_password, created_by, created_at
             ) VALUES (?, ?, ?, ?, ?, 'admin', 1, ?, NOW())"
        );
        $insert->execute([$firstName, $lastName, $email, $placeholder, $hash, $creatorId]);

        echo json_encode([
            'success' => true,
            'message' => 'Admin account created.',
            'id' => (int) $pdo->lastInsertId(),
        ]);
        exit;
    }

    if ($postAction === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        $selfId = (int) $_SESSION['user_id'];

        if ($id < 1) {
            jsonError('Admin ID is required.');
        }
        if ($id === $selfId) {
            jsonError('You cannot delete your own account.');
        }
        if ($id === 1) {
            jsonError('The original seeded admin cannot be deleted.');
        }

        $stmt = $pdo->prepare("SELECT id FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            jsonError('Admin account not found.', 404);
        }

        $delete = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'admin'");
        $delete->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Admin account deleted.']);
        exit;
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed.', 405);
