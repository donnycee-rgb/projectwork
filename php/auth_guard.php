<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id']) || empty($_SESSION['user_role'])) {
    header('Location: ../login.html');
    exit;
}

if (isset($required_role) && $_SESSION['user_role'] !== $required_role) {
    header('Location: ../login.html');
    exit;
}
