<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in and has the admin role
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    // Not an admin, redirect to front-end index
    header("Location: ../index.php?error=unauthorized");
    exit;
}
?>
