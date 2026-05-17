<?php
header('Content-Type: application/json');

require_once '../config/db.php';

$defaultFirst = 'System';
$defaultLast = 'Administrator';
$defaultEmail = 'admin@greenfield.edu';
$defaultStudentId = 'ADM-ROOT';
$defaultPassword = 'Admin@1234';

$existing = $pdo->query("SELECT id FROM users WHERE role = 'admin' ORDER BY id ASC LIMIT 1")->fetch();
if ($existing) {
    $ensure = $pdo->prepare('UPDATE users SET must_change_password = 0 WHERE id = ?');
    $ensure->execute([(int) $existing['id']]);
    echo json_encode([
        'success' => true,
        'message' => 'An admin account already exists. Policy has been updated.',
        'admin_id' => (int) $existing['id'],
    ]);
    exit;
}

$hash = password_hash($defaultPassword, PASSWORD_DEFAULT);
$insert = $pdo->prepare(
    "INSERT INTO users (
        first_name, last_name, email, student_id, password_hash, role, must_change_password, created_at
     ) VALUES (?, ?, ?, ?, ?, 'admin', 0, NOW())"
);
$insert->execute([$defaultFirst, $defaultLast, $defaultEmail, $defaultStudentId, $hash]);

echo json_encode([
    'success' => true,
    'message' => 'Default admin account created successfully.',
    'admin_id' => (int) $pdo->lastInsertId(),
    'email' => $defaultEmail,
]);
