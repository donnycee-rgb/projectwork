<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$stmt = $pdo->prepare(
    'SELECT id, first_name, last_name, email, student_id, password_hash, role
     FROM users WHERE email = ? LIMIT 1'
);
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

$_SESSION['user_id']        = (int) $user['id'];
$_SESSION['user_name']      = $user['first_name'];
$_SESSION['user_full_name'] = trim($user['first_name'] . ' ' . $user['last_name']);
$_SESSION['user_email']     = $user['email'];
$_SESSION['student_id']     = $user['student_id'];
$_SESSION['user_role']      = $user['role'];

$redirect = $user['role'] === 'admin'
    ? '/greenfield/admin/dashboard.html'
    : '/greenfield/student/dashboard.php';

echo json_encode([
    'success'  => true,
    'redirect' => $redirect,
    'user'     => [
        'name'  => trim($user['first_name'] . ' ' . $user['last_name']),
        'role'  => $user['role'],
        'email' => $user['email'],
    ],
]);