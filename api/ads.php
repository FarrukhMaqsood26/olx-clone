<?php
// api/ads.php
require_once '../includes/config.php';

// Auth check - Must be logged in to post ads
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php?error=login_required");
        exit;
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : '';


// 1. CREATE AD

if ($action == 'create' && $_SERVER["REQUEST_METHOD"] == "POST") {
    require_login();

    $user_id = $_SESSION['user_id'];
    $category_id = intval($_POST['category_id']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = floatval($_POST['price']);
    $condition_type = in_array($_POST['condition_type'], ['new', 'used']) ? $_POST['condition_type'] : 'used';
    $location = sanitize_input($_POST['location']);

    try {
        $pdo->beginTransaction();

        // Insert Ad
        $stmt = $pdo->prepare("INSERT INTO ads (user_id, category_id, title, description, price, condition_type, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $category_id, $title, $description, $price, $condition_type, $location]);

        $ad_id = $pdo->lastInsertId();

        // Handle file uploads
        if (isset($_FILES['images'])) {
            $upload_dir = '../assets/uploads/';
            if (!is_dir($upload_dir))
                mkdir($upload_dir, 0755, true);

            // Handle both multiple and single file uploads depending on input name
            $files = $_FILES['images'];
            $file_count = is_array($files['name']) ? count($files['name']) : 1;
            $is_primary = true; 

            for ($i = 0; $i < $file_count; $i++) {
                $file_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                if (empty($file_name)) continue;

                $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $file_size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                $file_error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];

                if ($file_error === UPLOAD_ERR_OK && in_array($file_ext, $allowed) && $file_size < 5000000) {
                    $new_file_name = uniqid('ad_', true) . '.' . $file_ext;
                    $dest_path = $upload_dir . $new_file_name;

                    if (move_uploaded_file($tmp_name, $dest_path)) {
                        $img_stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, ?)");
                        $img_stmt->execute([$ad_id, 'assets/uploads/' . $new_file_name, $is_primary ? 1 : 0]);
                        $is_primary = false;
                    }
                }
            }
        }

        $pdo->commit();
        
        // Return JSON if AJAX, otherwise redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'id' => $ad_id]);
        } else {
            header("Location: ../profile.php?success=ad_posted");
        }
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            die("Error posting ad: " . $e->getMessage());
        }
        exit;
    }
}

// ---------------------------------------------------------
// 2. FETCH RECENT ADS (AJAX Endpoint)
// ---------------------------------------------------------
if ($action == 'fetch_recent') {
    header('Content-Type: application/json');
    $stmt = $pdo->query("
        SELECT a.id, a.title, a.price, a.location, a.created_at, 
               (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
        FROM ads a
        WHERE a.status = 'active'
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $ads = $stmt->fetchAll();
    echo json_encode($ads);
    exit;
}

// ---------------------------------------------------------
// 3. DELETE AD
// ---------------------------------------------------------
if ($action == 'delete') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
    $check->execute([$ad_id, $user_id]);

    if ($check->rowCount() > 0) {
        // Delete associated images from disk
        $imgStmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = ?");
        $imgStmt->execute([$ad_id]);
        $images = $imgStmt->fetchAll();
        foreach ($images as $img) {
            $filepath = '../' . $img['image_path'];
            if (file_exists($filepath) && strpos($img['image_path'], 'assets/uploads/') === 0) {
                unlink($filepath);
            }
        }

        // Delete ad (cascade will remove images & messages)
        $delStmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
        $delStmt->execute([$ad_id, $user_id]);

        header("Location: ../profile.php?success=ad_deleted");
    } else {
        header("Location: ../profile.php?error=unauthorized");
    }
    exit;
}


// 4. MARK AD AS SOLD

if ($action == 'mark_sold') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE ads SET status = 'sold' WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $user_id]);

    if ($stmt->rowCount() > 0) {
        header("Location: ../profile.php?success=ad_updated");
    } else {
        header("Location: ../profile.php?error=unauthorized");
    }
    exit;
}

// ---------------------------------------------------------
// 5. REACTIVATE AD
// ---------------------------------------------------------
if ($action == 'reactivate') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("UPDATE ads SET status = 'active' WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $user_id]);

    header("Location: ../profile.php?success=ad_updated");
    exit;
}