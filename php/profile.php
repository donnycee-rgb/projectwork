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

if (empty($_SESSION['user_id'])) {
    jsonError('Unauthorized.', 401);
}

$userId = (int) $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET') {
    $stmt = $pdo->prepare('SELECT profile_photo FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$userId]);
    $photo = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'profile_photo' => $photo ?: null,
    ]);
    exit;
}

if ($method === 'POST') {
    if ($action !== 'upload') {
        jsonError('Unknown action.');
    }
    if (!isset($_FILES['photo'])) {
        jsonError('No photo file uploaded.');
    }

    $file = $_FILES['photo'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        jsonError('Upload failed. Please try another image.');
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        jsonError('Image must be 2MB or smaller.');
    }

    $tmpPath = $file['tmp_name'] ?? '';
    if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
        jsonError('Invalid upload.');
    }

    $mime = mime_content_type($tmpPath) ?: '';
    $map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($map[$mime])) {
        jsonError('Only JPG, PNG, and WEBP images are allowed.');
    }

    $ext = $map[$mime];
    $uploadDir = realpath(__DIR__ . '/../images');
    if ($uploadDir === false) {
        jsonError('Image folder is missing on the server.', 500);
    }
    $profileDir = $uploadDir . DIRECTORY_SEPARATOR . 'profiles';
    if (!is_dir($profileDir) && !mkdir($profileDir, 0755, true)) {
        jsonError('Could not prepare profile upload directory.', 500);
    }

    foreach (['jpg', 'png', 'webp'] as $oldExt) {
        $oldPath = $profileDir . DIRECTORY_SEPARATOR . 'user_' . $userId . '.' . $oldExt;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    $fileName = 'user_' . $userId . '.' . $ext;
    $targetPath = $profileDir . DIRECTORY_SEPARATOR . $fileName;
    if (!move_uploaded_file($tmpPath, $targetPath)) {
        jsonError('Could not save uploaded file.', 500);
    }

    $storedPath = 'images/profiles/' . $fileName;
    $stmt = $pdo->prepare('UPDATE users SET profile_photo = ? WHERE id = ?');
    $stmt->execute([$storedPath, $userId]);

    echo json_encode([
        'success' => true,
        'message' => 'Profile photo updated.',
        'profile_photo' => $storedPath,
    ]);
    exit;
}

jsonError('Method not allowed.', 405);
