<?php
header('Content-Type: application/json');

require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/db.php';

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? '';

if ($action === 'create') {
    if ($method !== 'POST') {
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }

    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        json_response(['success' => false, 'message' => 'Access denied.'], 403);
    }

    $data = read_json_body();
    $title = trim((string) ($data['title'] ?? ''));
    $message = trim((string) ($data['message'] ?? ''));
    $adminId = (int) ($_SESSION['user_id'] ?? 0);

    if ($title === '' || $message === '') {
        json_response(['success' => false, 'message' => 'Title and message are required.'], 422);
    }

    if ($adminId < 1) {
        json_response(['success' => false, 'message' => 'Invalid admin session.'], 401);
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO announcements (title, message, created_by, created_at)
             VALUES (?, ?, ?, NOW())'
        );
        $stmt->execute([$title, $message, $adminId]);
        json_response(['success' => true]);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => 'Could not create announcement.'], 500);
    }
}

if ($action === 'list') {
    if ($method !== 'GET') {
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }

    try {
        $stmt = $pdo->query(
            "SELECT a.id, a.title, a.message, a.created_at,
                    TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) AS admin_name
             FROM announcements a
             INNER JOIN users u ON u.id = a.created_by
             ORDER BY a.created_at DESC
             LIMIT 10"
        );

        $rows = array_map(static function (array $row): array {
            return [
                'id' => (int) $row['id'],
                'title' => $row['title'],
                'message' => $row['message'],
                'created_at' => $row['created_at'],
                'admin_name' => $row['admin_name'] ?: 'Admin',
            ];
        }, $stmt->fetchAll());

        json_response(['success' => true, 'announcements' => $rows]);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => 'Could not load announcements.'], 500);
    }
}

if ($action === 'delete') {
    if ($method !== 'POST') {
        json_response(['success' => false, 'message' => 'Method not allowed.'], 405);
    }
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        json_response(['success' => false, 'message' => 'Access denied.'], 403);
    }
    $data = read_json_body();
    $id = (int) ($data['id'] ?? 0);
    if ($id < 1) {
        json_response(['success' => false, 'message' => 'Invalid ID.'], 422);
    }
    try {
        $stmt = $pdo->prepare('DELETE FROM announcements WHERE id = ?');
        $stmt->execute([$id]);
        json_response(['success' => true]);
    } catch (Throwable $e) {
        json_response(['success' => false, 'message' => 'Could not delete announcement.'], 500);
    }
}

json_response(['success' => false, 'message' => 'Unknown action.'], 400);
