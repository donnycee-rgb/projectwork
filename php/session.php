<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

if (empty($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit;
}

$userId = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare(
    'SELECT id, first_name, last_name, email, role, student_id, must_change_password
     FROM users
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION = [];
    session_destroy();
    echo json_encode(['success' => false]);
    exit;
}

$fullName = trim($user['first_name'] . ' ' . $user['last_name']);
$mustChange = (bool) ((int) $user['must_change_password']);

$_SESSION['user_name'] = $user['first_name'];
$_SESSION['user_full_name'] = $fullName;
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['student_id'] = $user['student_id'];
$_SESSION['must_change_password'] = $mustChange;

echo json_encode([
    'success' => true,
    'user_id' => (int) $user['id'],
    'name' => $fullName,
    'email' => $user['email'],
    'role' => $user['role'],
    'student_id' => $user['student_id'],
    'must_change_password' => $mustChange,
]);
