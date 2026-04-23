<?php
require_once '../includes/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Unauthorized");
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

// --- ADS ACTIONS ---
if ($action == 'update_ad_status' && isset($_POST['ad_id']) && isset($_POST['status'])) {
    $ad_id = (int)$_POST['ad_id'];
    $status = $_POST['status'];
    
    if (in_array($status, ['active', 'pending', 'rejected', 'sold'])) {
        $stmt = $pdo->prepare("UPDATE ads SET status = ? WHERE id = ?");
        if($stmt->execute([$status, $ad_id])) {
            header("Location: ../admin/ads.php?success=Ad status updated");
            exit;
        }
    }
    header("Location: ../admin/ads.php?error=Failed to update status");
    exit;
}

if ($action == 'delete_ad' && isset($_POST['ad_id'])) {
    $ad_id = (int)$_POST['ad_id'];
    $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ?");
    if($stmt->execute([$ad_id])) {
        header("Location: ../admin/ads.php?success=Ad deleted successfully");
        exit;
    }
    header("Location: ../admin/ads.php?error=Failed to delete ad");
    exit;
}

// --- CATEGORY ACTIONS ---
if ($action == 'add_category' && isset($_POST['name']) && isset($_POST['slug'])) {
    $name = sanitize_input($_POST['name']);
    $slug = sanitize_input($_POST['slug']);
    $icon = sanitize_input($_POST['icon']);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
        $stmt->execute([$name, $slug, $icon]);
        header("Location: ../admin/categories.php?success=Category added");
    } catch(Exception $e) {
        header("Location: ../admin/categories.php?error=Failed to add. Slug might exist.");
    }
    exit;
}

if ($action == 'delete_category' && isset($_POST['category_id'])) {
    $id = (int)$_POST['category_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: ../admin/categories.php?success=Category deleted");
    } catch(Exception $e) {
        header("Location: ../admin/categories.php?error=Cannot delete category with active ads.");
    }
    exit;
}

// --- USER ACTIONS ---
if ($action == 'update_user_role' && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id === $_SESSION['user_id']) {
        header("Location: ../admin/users.php?error=Cannot change your own role");
        exit;
    }
    
    $role = $_POST['role'];
    if (in_array($role, ['user', 'admin'])) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
        header("Location: ../admin/users.php?success=User role updated");
        exit;
    }
}

if ($action == 'delete_user' && isset($_POST['user_id'])) {
    $user_id = (int)$_POST['user_id'];
    if ($user_id === $_SESSION['user_id']) {
        header("Location: ../admin/users.php?error=Cannot delete your own account");
        exit;
    }
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    if($stmt->execute([$user_id])) {
        header("Location: ../admin/users.php?success=User deleted");
        exit;
    }
}

header("Location: ../admin/index.php");
exit;
