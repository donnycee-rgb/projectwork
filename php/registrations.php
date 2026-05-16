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

function requireStudent(): void
{
    requireAuth();
    if (($_SESSION['user_role'] ?? '') !== 'student') {
        jsonError('Only students can perform this action.', 403);
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

function fetchCostPerCredit(PDO $pdo): float
{
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'cost_per_credit' LIMIT 1");
    $stmt->execute();
    $val = $stmt->fetchColumn();
    if ($val === false) {
        return 5000.0;
    }
    $num = (float) $val;
    return $num > 0 ? $num : 5000.0;
}

function fetchStudentCreditSnapshot(PDO $pdo, int $userId): array
{
    $feesStmt = $pdo->prepare('SELECT fees_paid FROM users WHERE id = ? LIMIT 1');
    $feesStmt->execute([$userId]);
    $feesPaid = (float) ($feesStmt->fetchColumn() ?? 0);

    $usedStmt = $pdo->prepare(
        'SELECT COALESCE(SUM(c.credits), 0) AS credits_used
         FROM registrations r
         INNER JOIN courses c ON c.id = r.course_id
         WHERE r.user_id = ?'
    );
    $usedStmt->execute([$userId]);
    $creditsUsed = (int) ($usedStmt->fetchColumn() ?? 0);

    $costPerCredit = fetchCostPerCredit($pdo);
    $creditsAvailable = $costPerCredit > 0 ? (int) floor($feesPaid / $costPerCredit) : 0;
    $creditsRemaining = $creditsAvailable - $creditsUsed;

    return [
        'fees_paid' => $feesPaid,
        'cost_per_credit' => $costPerCredit,
        'credits_used' => $creditsUsed,
        'credits_available' => $creditsAvailable,
        'credits_remaining' => $creditsRemaining,
    ];
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    requireAuth();

    if ($action === 'all') {
        requireAdmin();
        $stmt = $pdo->query(
            'SELECT r.id, r.registered_at,
                    u.first_name, u.last_name, u.student_id,
                    c.title AS course_title, c.code AS course_code,
                    c.department, c.credits
             FROM registrations r
             INNER JOIN users u ON u.id = r.user_id
             INNER JOIN courses c ON c.id = r.course_id
             ORDER BY r.registered_at DESC'
        );
        echo json_encode(['success' => true, 'registrations' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'recent') {
        requireAdmin();
        $stmt = $pdo->query(
            'SELECT r.id, r.registered_at,
                    u.first_name, u.last_name, u.student_id,
                    c.title AS course_title, c.code AS course_code,
                    c.department
             FROM registrations r
             INNER JOIN users u ON u.id = r.user_id
             INNER JOIN courses c ON c.id = r.course_id
             ORDER BY r.registered_at DESC
             LIMIT 10'
        );
        echo json_encode(['success' => true, 'registrations' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'students') {
        requireAdmin();
        $stmt = $pdo->query(
            "SELECT u.id, u.first_name, u.last_name, u.email, u.student_id, u.created_at,
                    u.fees_paid,
                    COUNT(r.id) AS enrolled_count
             FROM users u
             LEFT JOIN registrations r ON r.user_id = u.id
             WHERE u.role = 'student'
             GROUP BY u.id, u.first_name, u.last_name, u.email, u.student_id, u.created_at, u.fees_paid
             ORDER BY u.last_name, u.first_name"
        );
        echo json_encode(['success' => true, 'students' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'available') {
        requireStudent();
        $userId = (int) $_SESSION['user_id'];
        $stmt = $pdo->prepare(
            'SELECT c.id, c.title, c.code, c.department, c.instructor,
                    c.credits, c.capacity, c.enrolled, c.description,
                    (c.capacity - c.enrolled) AS slots_remaining
             FROM courses c
             WHERE c.enrolled < c.capacity
               AND c.id NOT IN (
                   SELECT course_id FROM registrations WHERE user_id = ?
               )
             ORDER BY c.department, c.title'
        );
        $stmt->execute([$userId]);
        echo json_encode(['success' => true, 'courses' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'stats') {
        if (($_SESSION['user_role'] ?? '') === 'admin') {
            requireAdmin();

            $totalStudents = (int) $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'student'")->fetchColumn();
            $totalCourses = (int) $pdo->query('SELECT COUNT(id) FROM courses')->fetchColumn();
            $totalRegistrations = (int) $pdo->query('SELECT COUNT(id) FROM registrations')->fetchColumn();
            $departmentsCount = (int) $pdo->query('SELECT COUNT(DISTINCT department) FROM courses')->fetchColumn();

            $popular = $pdo->query(
                'SELECT c.title, COUNT(r.id) AS reg_count
                 FROM courses c
                 LEFT JOIN registrations r ON r.course_id = c.id
                 GROUP BY c.id, c.title
                 ORDER BY reg_count DESC, c.title ASC
                 LIMIT 1'
            )->fetch();

            echo json_encode([
                'success' => true,
                'stats' => [
                    'total_students' => $totalStudents,
                    'total_courses' => $totalCourses,
                    'total_registrations' => $totalRegistrations,
                    'departments_count' => $departmentsCount,
                    'most_popular_course' => $popular ? $popular['title'] : '—',
                ],
            ]);
            exit;
        }

        requireStudent();
        $userId = (int) $_SESSION['user_id'];

        $enrolled = $pdo->prepare(
            'SELECT COUNT(r.id) AS course_count,
                    COALESCE(SUM(c.credits), 0) AS total_credits
             FROM registrations r
             INNER JOIN courses c ON c.id = r.course_id
             WHERE r.user_id = ?'
        );
        $enrolled->execute([$userId]);
        $counts = $enrolled->fetch();

        $slots = $pdo->prepare(
            'SELECT COALESCE(SUM(c.capacity - c.enrolled), 0) AS open_slots
             FROM courses c
             WHERE c.enrolled < c.capacity
               AND c.id NOT IN (
                   SELECT course_id FROM registrations WHERE user_id = ?
               )'
        );
        $slots->execute([$userId]);
        $openSlots = (int) ($slots->fetchColumn() ?? 0);

        $credits = fetchStudentCreditSnapshot($pdo, $userId);
        $usedCourses = $pdo->prepare(
            'SELECT c.id, c.title, c.code, c.credits
             FROM registrations r
             INNER JOIN courses c ON c.id = r.course_id
             WHERE r.user_id = ?
             ORDER BY c.title'
        );
        $usedCourses->execute([$userId]);

        echo json_encode([
            'success' => true,
            'stats' => [
                'enrolled_courses' => (int) ($counts['course_count'] ?? 0),
                'total_credits' => (int) ($counts['total_credits'] ?? 0),
                'available_slots' => $openSlots,
                'fees_paid' => number_format($credits['fees_paid'], 2, '.', ''),
                'cost_per_credit' => number_format($credits['cost_per_credit'], 2, '.', ''),
                'credits_available' => $credits['credits_available'],
                'credits_used' => $credits['credits_used'],
                'credits_remaining' => $credits['credits_remaining'],
                'credit_courses' => $usedCourses->fetchAll(),
            ],
        ]);
        exit;
    }

    $requestedId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $_SESSION['user_id'];
    if (($_SESSION['user_role'] ?? '') === 'student' && $requestedId !== (int) $_SESSION['user_id']) {
        jsonError('Access denied.', 403);
    }

    $stmt = $pdo->prepare(
        'SELECT r.id AS registration_id, r.registered_at,
                c.id, c.title, c.code, c.department, c.instructor,
                c.credits, c.capacity, c.enrolled, c.description
         FROM registrations r
         INNER JOIN courses c ON c.id = r.course_id
         WHERE r.user_id = ?
         ORDER BY r.registered_at DESC'
    );
    $stmt->execute([$requestedId]);
    echo json_encode(['success' => true, 'courses' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    requireStudent();
    $data = readJsonBody();
    $postAction = $data['action'] ?? $action;
    $courseId = (int) ($data['course_id'] ?? 0);
    $userId = (int) $_SESSION['user_id'];

    if ($courseId < 1) {
        jsonError('Valid course ID is required.');
    }

    if ($postAction === 'enroll') {
        try {
            $pdo->beginTransaction();

            $courseStmt = $pdo->prepare(
                'SELECT id, capacity, enrolled, credits
                 FROM courses
                 WHERE id = ?
                 FOR UPDATE'
            );
            $courseStmt->execute([$courseId]);
            $course = $courseStmt->fetch();
            if (!$course) {
                $pdo->rollBack();
                jsonError('Course not found.', 404);
            }
            if ((int) $course['enrolled'] >= (int) $course['capacity']) {
                $pdo->rollBack();
                jsonError('This course is full.');
            }

            $existsStmt = $pdo->prepare(
                'SELECT id FROM registrations WHERE user_id = ? AND course_id = ? LIMIT 1'
            );
            $existsStmt->execute([$userId, $courseId]);
            if ($existsStmt->fetch()) {
                $pdo->rollBack();
                jsonError('You are already enrolled in this course.');
            }

            $feesStmt = $pdo->prepare('SELECT fees_paid FROM users WHERE id = ? FOR UPDATE');
            $feesStmt->execute([$userId]);
            $feesPaid = (float) ($feesStmt->fetchColumn() ?? 0);

            $creditsStmt = $pdo->prepare(
                'SELECT COALESCE(SUM(c.credits), 0) AS credits_used
                 FROM registrations r
                 INNER JOIN courses c ON c.id = r.course_id
                 WHERE r.user_id = ?'
            );
            $creditsStmt->execute([$userId]);
            $creditsUsed = (int) ($creditsStmt->fetchColumn() ?? 0);

            $costPerCredit = fetchCostPerCredit($pdo);
            $creditsAvailable = $costPerCredit > 0 ? (int) floor($feesPaid / $costPerCredit) : 0;
            $nextCreditsUsed = $creditsUsed + (int) $course['credits'];
            if ($nextCreditsUsed > $creditsAvailable) {
                $pdo->rollBack();
                jsonError('Insufficient credits. Please contact administration to update your fees.');
            }

            $insertStmt = $pdo->prepare(
                'INSERT INTO registrations (user_id, course_id, registered_at)
                 VALUES (?, ?, NOW())'
            );
            $insertStmt->execute([$userId, $courseId]);

            $updateCourse = $pdo->prepare('UPDATE courses SET enrolled = enrolled + 1 WHERE id = ?');
            $updateCourse->execute([$courseId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Enrolled successfully.']);
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonError('Enrollment failed. Please try again.');
        }
    }

    if ($postAction === 'unenroll') {
        try {
            $pdo->beginTransaction();

            $regStmt = $pdo->prepare(
                'SELECT id FROM registrations WHERE user_id = ? AND course_id = ? LIMIT 1'
            );
            $regStmt->execute([$userId, $courseId]);
            $registration = $regStmt->fetch();
            if (!$registration) {
                $pdo->rollBack();
                jsonError('You are not enrolled in this course.');
            }

            $del = $pdo->prepare('DELETE FROM registrations WHERE id = ?');
            $del->execute([(int) $registration['id']]);

            $dec = $pdo->prepare('UPDATE courses SET enrolled = GREATEST(enrolled - 1, 0) WHERE id = ?');
            $dec->execute([$courseId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Unenrolled successfully.']);
            exit;
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonError('Unenrollment failed. Please try again.');
        }
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed.', 405);