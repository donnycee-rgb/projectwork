<?php
require_once '../config/db.php';

$hash = password_hash('Admin@1234', PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    "INSERT INTO users (first_name, last_name, email, student_id, password_hash, role)
     VALUES ('Admin', 'Greenfield', 'admin@greenfield.edu', 'ADM000-0000/2024', ?, 'admin')
     ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)"
);
$stmt->execute([$hash]);

header('Content-Type: text/plain; charset=utf-8');
echo "Admin account ready.\nEmail: admin@greenfield.edu\nPassword: Admin@1234\nDelete this file after use.\n";
