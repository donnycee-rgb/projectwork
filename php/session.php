<?php
header('Content-Type: application/json');
session_start();

if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

echo json_encode([
    'success' => true,
    'name'    => $_SESSION['user_full_name'] ?? $_SESSION['user_name'] ?? 'Admin',
    'email'   => $_SESSION['user_email'] ?? '',
    'role'    => 'admin',
]);
