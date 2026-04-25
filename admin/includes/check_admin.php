<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and has the admin role
$role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : 'user';
if (!isset($_SESSION['user_id']) || $role !== 'admin') {
    // Not an admin, redirect to front-end index
    header("Location: ../index.php?error=unauthorized");
    exit;
}
?>
