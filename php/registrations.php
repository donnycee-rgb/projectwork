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
    if ($_SESSION['user_role'] !== 'admin') {
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
            'SELECT u.id, u.first_name, u.last_name, u.email, u.student_id, u.created_at,
                    COUNT(r.id) AS enrolled_count
             FROM users u
             LEFT JOIN registrations r ON r.user_id = u.id
             WHERE u.role = \'student\'
             GROUP BY u.id, u.first_name, u.last_name, u.email, u.student_id, u.created_at
             ORDER BY u.last_name, u.first_name'
        );

        echo json_encode(['success' => true, 'students' => $stmt->fetchAll()]);
        exit;
    }

    if ($action === 'available') {
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

            $totalStudents = (int) $pdo->query(
                "SELECT COUNT(id) FROM users WHERE role = 'student'"
            )->fetchColumn();

            $totalCourses = (int) $pdo->query(
                'SELECT COUNT(id) FROM courses'
            )->fetchColumn();

            $totalRegistrations = (int) $pdo->query(
                'SELECT COUNT(id) FROM registrations'
            )->fetchColumn();

            $departmentsCount = (int) $pdo->query(
                'SELECT COUNT(DISTINCT department) FROM courses'
            )->fetchColumn();

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
                    'total_students'       => $totalStudents,
                    'total_courses'        => $totalCourses,
                    'total_registrations'  => $totalRegistrations,
                    'departments_count'    => $departmentsCount,
                    'most_popular_course'  => $popular ? $popular['title'] : '—',
                ],
            ]);
            exit;
        }

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
        $open = $slots->fetch();

        echo json_encode([
            'success' => true,
            'stats' => [
                'enrolled_courses' => (int) $counts['course_count'],
                'total_credits'    => (int) $counts['total_credits'],
                'available_slots'  => (int) $open['open_slots'],
            ],
        ]);
        exit;
    }

    $requestedId = isset($_GET['user_id']) ? (int) $_GET['user_id'] : (int) $_SESSION['user_id'];

    if ($_SESSION['user_role'] === 'student' && $requestedId !== (int) $_SESSION['user_id']) {
        jsonError('Access denied.', 403);
    }

    $stmt = $pdo->prepare(
        'SELECT r.id AS registration_id, r.registered_at,
                c.id, c.title, c.code, c.department, c.instructor,
                c.credits, c.capacity, c.enrolled, c.description
         FROM registrations r
         INNER JOIN courses c ON c.id = r.course_id
         WHERE r.user_id = ?
         ORDER BY c.title'
    );
    $stmt->execute([$requestedId]);

    echo json_encode(['success' => true, 'courses' => $stmt->fetchAll()]);
    exit;
}

if ($method === 'POST') {
    requireAuth();

    if ($_SESSION['user_role'] !== 'student') {
        jsonError('Only students can enroll or unenroll.', 403);
    }

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

            $course = $pdo->prepare(
                'SELECT id, capacity, enrolled FROM courses WHERE id = ? FOR UPDATE'
            );
            $course->execute([$courseId]);
            $row = $course->fetch();

            if (!$row) {
                $pdo->rollBack();
                jsonError('Course not found.', 404);
            }

            if ((int) $row['enrolled'] >= (int) $row['capacity']) {
                $pdo->rollBack();
                jsonError('This course is full.');
            }

            $existing = $pdo->prepare(
                'SELECT id FROM registrations WHERE user_id = ? AND course_id = ? LIMIT 1'
            );
            $existing->execute([$userId, $courseId]);
            if ($existing->fetch()) {
                $pdo->rollBack();
                jsonError('You are already enrolled in this course.');
            }

            $reg = $pdo->prepare(
                'INSERT INTO registrations (user_id, course_id, registered_at) VALUES (?, ?, NOW())'
            );
            $reg->execute([$userId, $courseId]);

            $inc = $pdo->prepare('UPDATE courses SET enrolled = enrolled + 1 WHERE id = ?');
            $inc->execute([$courseId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Enrolled successfully.']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonError('Enrollment failed. Please try again.');
        }
        exit;
    }

    if ($postAction === 'unenroll') {
        try {
            $pdo->beginTransaction();

            $reg = $pdo->prepare(
                'SELECT id FROM registrations WHERE user_id = ? AND course_id = ? LIMIT 1'
            );
            $reg->execute([$userId, $courseId]);
            $registration = $reg->fetch();

            if (!$registration) {
                $pdo->rollBack();
                jsonError('You are not enrolled in this course.');
            }

            $del = $pdo->prepare('DELETE FROM registrations WHERE id = ?');
            $del->execute([$registration['id']]);

            $dec = $pdo->prepare(
                'UPDATE courses SET enrolled = GREATEST(enrolled - 1, 0) WHERE id = ?'
            );
            $dec->execute([$courseId]);

            $pdo->commit();
            echo json_encode(['success' => true, 'message' => 'Unenrolled successfully.']);
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            jsonError('Unenrollment failed. Please try again.');
        }
        exit;
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed.', 405);
