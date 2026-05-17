<?php
header('Content-Type: application/json');
session_start();

require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$first      = trim($_POST['first_name'] ?? '');
$last       = trim($_POST['last_name']  ?? '');
$email      = trim($_POST['email']      ?? '');
$student_id = trim($_POST['student_id'] ?? '');
$password   = $_POST['password']        ?? '';

if (!$first || !$last || !$email || !$student_id || !$password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

if (!preg_match('/^[A-Z]{3}\d{3}-\d{4}\/\d{4}$/i', $student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID must follow format SCM211-XXXX/YYYY.']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

$check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR student_id = ? LIMIT 1");
$check->execute([$email, $student_id]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'An account with this email or student ID already exists.']);
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$insert = $pdo->prepare("
    INSERT INTO users (
        first_name, last_name, email, student_id, password_hash, role,
        must_change_password, fees_paid, created_at
    )
    VALUES (?, ?, ?, ?, ?, 'student', 0, 0.00, NOW())
");

try {
    $insert->execute([$first, $last, $email, strtoupper($student_id), $hash]);
    $userId = $pdo->lastInsertId();

    $_SESSION['user_id']        = (int) $userId;
    $_SESSION['user_name']      = $first;
    $_SESSION['user_full_name'] = trim($first . ' ' . $last);
    $_SESSION['user_email']     = $email;
    $_SESSION['student_id']     = strtoupper($student_id);
    $_SESSION['user_role']      = 'student';
    $_SESSION['must_change_password'] = false;

    echo json_encode(['success' => true, 'redirect' => 'student/dashboard.php']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
}