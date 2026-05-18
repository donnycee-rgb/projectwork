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

function validateCourseFields(array $data, bool $requireId = false): array
{
    $title       = trim($data['title'] ?? '');
    $code        = strtoupper(trim($data['code'] ?? ''));
    $department  = trim($data['department'] ?? '');
    $instructor  = trim($data['instructor'] ?? '');
    $credits     = (int) ($data['credits'] ?? 0);
    $capacity    = (int) ($data['capacity'] ?? 0);
    $description = trim($data['description'] ?? '');
    $id          = isset($data['id']) ? (int) $data['id'] : 0;

    if ($requireId && $id < 1) {
        jsonError('Course ID is required.');
    }
    if (!$title || !$code || !$department || !$instructor) {
        jsonError('Title, code, department, and instructor are required.');
    }
    if ($credits < 1 || $credits > 12) {
        jsonError('Credits must be between 1 and 12.');
    }
    if ($capacity < 1 || $capacity > 500) {
        jsonError('Capacity must be between 1 and 500.');
    }

    return compact('title', 'code', 'department', 'instructor', 'credits', 'capacity', 'description', 'id');
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    requireAuth();

    if ($action === 'stats') {
        requireAdmin();

        $students = (int) $pdo->query(
            "SELECT COUNT(id) FROM users WHERE role = 'student'"
        )->fetchColumn();

        $courses = (int) $pdo->query(
            'SELECT COUNT(id) FROM courses'
        )->fetchColumn();

        $registrations = (int) $pdo->query(
            'SELECT COUNT(id) FROM registrations'
        )->fetchColumn();

        $departments = (int) $pdo->query(
            'SELECT COUNT(DISTINCT department) FROM courses'
        )->fetchColumn();

        echo json_encode([
            'success' => true,
            'stats' => [
                'students'      => $students,
                'courses'       => $courses,
                'registrations' => $registrations,
                'departments'   => $departments,
            ],
        ]);
        exit;
    }

    if ($action === 'admin') {
        requireAdmin();

        $stmt = $pdo->query(
            'SELECT c.id, c.title, c.code, c.department, c.instructor,
                    c.credits, c.capacity, c.enrolled, c.description, c.created_at,
                    (c.capacity - c.enrolled) AS slots_remaining
             FROM courses c
             ORDER BY c.department, c.title'
        );
        $courses = $stmt->fetchAll();

        echo json_encode(['success' => true, 'courses' => $courses]);
        exit;
    }

    $userId = (int) $_SESSION['user_id'];

    $stmt = $pdo->prepare(
        'SELECT c.id, c.title, c.code, c.department, c.instructor,
                c.credits, c.capacity, c.enrolled, c.description,
                (c.capacity - c.enrolled) AS slots_remaining,
                CASE WHEN r.id IS NOT NULL THEN 1 ELSE 0 END AS is_enrolled
         FROM courses c
         LEFT JOIN registrations r ON r.course_id = c.id AND r.user_id = ?
         ORDER BY c.department, c.title'
    );
    $stmt->execute([$userId]);
    $courses = $stmt->fetchAll();

    echo json_encode(['success' => true, 'courses' => $courses]);
    exit;
}

if ($method === 'POST') {
    requireAdmin();
    $data = readJsonBody();
    $postAction = $data['action'] ?? $action;

    if ($postAction === 'add') {
        $fields = validateCourseFields($data);

        $dup = $pdo->prepare('SELECT id FROM courses WHERE code = ? LIMIT 1');
        $dup->execute([$fields['code']]);
        if ($dup->fetch()) {
            jsonError('A course with this code already exists.');
        }

        $insert = $pdo->prepare(
            'INSERT INTO courses (title, code, department, instructor, credits, capacity, description, enrolled)
             VALUES (?, ?, ?, ?, ?, ?, ?, 0)'
        );
        $insert->execute([
            $fields['title'],
            $fields['code'],
            $fields['department'],
            $fields['instructor'],
            $fields['credits'],
            $fields['capacity'],
            $fields['description'],
        ]);

        echo json_encode(['success' => true, 'message' => 'Course added.', 'id' => (int) $pdo->lastInsertId()]);
        exit;
    }

    if ($postAction === 'edit') {
        $fields = validateCourseFields($data, true);

        $exists = $pdo->prepare('SELECT id, enrolled FROM courses WHERE id = ? LIMIT 1');
        $exists->execute([$fields['id']]);
        $course = $exists->fetch();
        if (!$course) {
            jsonError('Course not found.', 404);
        }

        $dup = $pdo->prepare('SELECT id FROM courses WHERE code = ? AND id != ? LIMIT 1');
        $dup->execute([$fields['code'], $fields['id']]);
        if ($dup->fetch()) {
            jsonError('Another course already uses this code.');
        }

        if ($fields['capacity'] < (int) $course['enrolled']) {
            jsonError('Capacity cannot be less than current enrollment.');
        }

        $update = $pdo->prepare(
            'UPDATE courses
             SET title = ?, code = ?, department = ?, instructor = ?,
                 credits = ?, capacity = ?, description = ?
             WHERE id = ?'
        );
        $update->execute([
            $fields['title'],
            $fields['code'],
            $fields['department'],
            $fields['instructor'],
            $fields['credits'],
            $fields['capacity'],
            $fields['description'],
            $fields['id'],
        ]);

        echo json_encode(['success' => true, 'message' => 'Course updated.']);
        exit;
    }

    if ($postAction === 'delete') {
        $id = (int) ($data['id'] ?? 0);
        if ($id < 1) {
            jsonError('Course ID is required.');
        }

        $course = $pdo->prepare('SELECT id, enrolled FROM courses WHERE id = ? LIMIT 1');
        $course->execute([$id]);
        $row = $course->fetch();
        if (!$row) {
            jsonError('Course not found.', 404);
        }
        if ((int) $row['enrolled'] > 0) {
            jsonError('Cannot delete a course with enrolled students.');
        }

        $delete = $pdo->prepare('DELETE FROM courses WHERE id = ?');
        $delete->execute([$id]);

        echo json_encode(['success' => true, 'message' => 'Course deleted.']);
        exit;
    }

    jsonError('Unknown action.');
}

jsonError('Method not allowed.', 405);
