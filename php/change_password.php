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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Method not allowed.', 405);
}

if (empty($_SESSION['user_id'])) {
    jsonError('Unauthorized.', 401);
}

$raw = file_get_contents('php://input');
$data = [];
if ($raw) {
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $data = $decoded;
    }
}
if (!$data) {
    $data = $_POST;
}

$currentPassword = (string) ($data['current_password'] ?? '');
$newPassword = (string) ($data['new_password'] ?? '');
$confirmPassword = (string) ($data['confirm_password'] ?? '');

if ($newPassword === '' || $confirmPassword === '') {
    jsonError('New password and confirmation are required.');
}
if (strlen($newPassword) < 8) {
    jsonError('New password must be at least 8 characters.');
}
if ($newPassword !== $confirmPassword) {
    jsonError('New password and confirmation do not match.');
}

$userId = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare(
    'SELECT id, role, password_hash, must_change_password
     FROM users
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    jsonError('User account not found.', 404);
}

$mustChange = (int) $user['must_change_password'] === 1;
if (!$mustChange) {
    if ($currentPassword === '') {
        jsonError('Current password is required.');
    }
    if (!password_verify($currentPassword, $user['password_hash'])) {
        jsonError('Current password is incorrect.');
    }
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$update = $pdo->prepare(
    'UPDATE users
     SET password_hash = ?, must_change_password = 0
     WHERE id = ?'
);
$update->execute([$newHash, $userId]);

$_SESSION['must_change_password'] = false;

echo json_encode([
    'success' => true,
    'message' => 'Password updated successfully.',
    'role' => $user['role'],
]);
