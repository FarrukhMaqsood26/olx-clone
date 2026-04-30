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
    
    // START DEBUG LOGGING IMMEDIATELY
    $debugFile = __DIR__ . '/upload_debug.txt';
    $logData = "--- NEW POST ATTEMPT " . date('Y-m-d H:i:s') . " ---\n";
    $logData .= "FILES KEY: " . implode(', ', array_keys($_FILES)) . "\n";
    $logData .= "FILES: " . print_r($_FILES, true) . "\n";
    file_put_contents($debugFile, $logData, FILE_APPEND);

    $user_id = $_SESSION['user_id'];
    $category_id = intval($_POST['category_id']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = floatval($_POST['price']);
    $condition_type = in_array($_POST['condition_type'], ['new', 'used']) ? $_POST['condition_type'] : 'used';
    $location = sanitize_input($_POST['location']);

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO ads (user_id, category_id, title, description, price, condition_type, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([$user_id, $category_id, $title, $description, $price, $condition_type, $location]);
        $ad_id = $pdo->lastInsertId();
        $upload_dir = __DIR__ . '/../uploads/ads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    // Handle file uploads - check both possible keys
    $fileKey = isset($_FILES['images']) ? 'images' : (isset($_FILES['images[]']) ? 'images[]' : null);
    
    if ($fileKey) {
        $files = $_FILES[$fileKey];
        $file_count = is_array($files['name']) ? count($files['name']) : 1;
        $is_primary = true; 

        for ($i = 0; $i < $file_count; $i++) {
            $f_name  = is_array($files['name']) ? $files['name'][$i] : $files['name'];
            $f_tmp   = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
            $f_err   = is_array($files['error']) ? $files['error'][$i] : $files['error'];
            $f_size  = is_array($files['size']) ? $files['size'][$i] : $files['size'];
            $f_type  = is_array($files['type']) ? $files['type'][$i] : $files['type'];

            if (empty($f_name)) continue;

            $f_ext = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));
            if (empty($f_ext)) {
                if ($f_type === 'image/jpeg') $f_ext = 'jpg';
                elseif ($f_type === 'image/png') $f_ext = 'png';
                elseif ($f_type === 'image/webp') $f_ext = 'webp';
                else $f_ext = 'png';
            }

            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if ($f_err === UPLOAD_ERR_OK && in_array($f_ext, $allowed) && $f_size < 20000000) {
                $new_name = uniqid('ad_', true) . '.' . $f_ext;
                $dest = $upload_dir . $new_name;

                if (move_uploaded_file($f_tmp, $dest)) {
                    $img_stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, ?)");
                    $img_stmt->execute([$ad_id, $new_name, $is_primary ? 1 : 0]);
                    $is_primary = false;
                    file_put_contents($debugFile, "SUCCESS: $new_name\n", FILE_APPEND);
                } else {
                    file_put_contents($debugFile, "FAIL: move_uploaded_file to $dest\n", FILE_APPEND);
                }
            } else {
                file_put_contents($debugFile, "SKIP: Err $f_err, Ext $f_ext, Size $f_size\n", FILE_APPEND);
            }
        }
    } else {
        file_put_contents($debugFile, "NO FILES RECEIVED (Tried 'images' and 'images[]')\n", FILE_APPEND);
    }

        $pdo->commit();
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => true, 'id' => $ad_id]);
        } else {
            header("Location: ../profile.php?success=ad_posted");
        }
        exit;

    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errorMsg = $e->getMessage();
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        } else {
            die("Error: " . $errorMsg);
        }
        exit;
    }
}

// 2. FETCH RECENT ADS
if ($action == 'fetch_recent') {
    header('Content-Type: application/json');
    $stmt = $pdo->query("SELECT v.*, v.primary_image as main_image FROM view_active_ads v ORDER BY v.created_at DESC LIMIT 10");
    $ads = $stmt->fetchAll();
    echo json_encode($ads);
    exit;
}

// 3. DELETE AD
if ($action == 'delete') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $check = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
    $check->execute([$ad_id, $user_id]);
    if ($check->rowCount() > 0) {
        $delStmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND user_id = ?");
        $delStmt->execute([$ad_id, $user_id]);
        header("Location: ../profile.php?success=ad_deleted");
    } else {
        header("Location: ../profile.php?error=unauthorized");
    }
    exit;
}

// 4. MARK AS SOLD
if ($action == 'mark_sold') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE ads SET status = 'sold' WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $user_id]);
    header("Location: ../profile.php?success=ad_updated");
    exit;
}

// 5. REACTIVATE
if ($action == 'reactivate') {
    require_login();
    $ad_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE ads SET status = 'active' WHERE id = ? AND user_id = ?");
    $stmt->execute([$ad_id, $user_id]);
    header("Location: ../profile.php?success=ad_updated");
    exit;
}

// 6. UPDATE AD
if ($action == 'update' && $_SERVER["REQUEST_METHOD"] == "POST") {
    require_login();
    $user_id = $_SESSION['user_id'];
    $ad_id = intval($_GET['id']);
    $check = $pdo->prepare("SELECT id FROM ads WHERE id = ? AND user_id = ?");
    $check->execute([$ad_id, $user_id]);
    if ($check->rowCount() === 0) { echo json_encode(['success' => false, 'message' => 'Unauthorized']); exit; }

    $category_id = intval($_POST['category_id']);
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $price = floatval($_POST['price']);
    $condition_type = in_array($_POST['condition_type'], ['new', 'used']) ? $_POST['condition_type'] : 'used';
    $location = sanitize_input($_POST['location']);

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("UPDATE ads SET category_id = ?, title = ?, description = ?, price = ?, condition_type = ?, location = ?, status = 'pending' WHERE id = ?");
        $stmt->execute([$category_id, $title, $description, $price, $condition_type, $location, $ad_id]);

        // 1. HANDLE DELETIONS
        if (isset($_POST['delete_images']) && is_array($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $img_id) {
                // Get filename first to unlink
                $getImg = $pdo->prepare("SELECT image_path FROM ad_images WHERE id = ? AND ad_id = ?");
                $getImg->execute([$img_id, $ad_id]);
                $imgPath = $getImg->fetchColumn();

                if ($imgPath) {
                    // Delete record
                    $pdo->prepare("DELETE FROM ad_images WHERE id = ?")->execute([$img_id]);
                    // Delete file
                    $file = __DIR__ . '/../uploads/ads/' . $imgPath;
                    if (file_exists($file)) @unlink($file);
                }
            }
        }

        // 2. ENSURE PRIMARY EXISTS (if we deleted the primary, pick the next available)
        $checkPrimary = $pdo->prepare("SELECT COUNT(*) FROM ad_images WHERE ad_id = ? AND is_primary = 1");
        $checkPrimary->execute([$ad_id]);
        if ($checkPrimary->fetchColumn() == 0) {
            $setNewPrimary = $pdo->prepare("UPDATE ad_images SET is_primary = 1 WHERE ad_id = ? LIMIT 1");
            $setNewPrimary->execute([$ad_id]);
        }

        // 3. HANDLE NEW UPLOADS
        if (isset($_FILES['images'])) {
            $upload_dir = __DIR__ . '/../uploads/ads/';
            $files = $_FILES['images'];
            $file_count = is_array($files['name']) ? count($files['name']) : 1;
            $checkPrimary = $pdo->prepare("SELECT COUNT(*) FROM ad_images WHERE ad_id = ? AND is_primary = 1");
            $checkPrimary->execute([$ad_id]);
            $has_primary = ($checkPrimary->fetchColumn() > 0);

            for ($i = 0; $i < $file_count; $i++) {
                $f_name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                if (empty($f_name)) continue;
                $f_tmp = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $f_err = is_array($files['error']) ? $files['error'][$i] : $files['error'];
                $f_size = is_array($files['size']) ? $files['size'][$i] : $files['size'];
                $f_type = is_array($files['type']) ? $files['type'][$i] : $files['type'];
                $f_ext = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));
                if (empty($f_ext)) {
                    if ($f_type === 'image/jpeg') $f_ext = 'jpg';
                    elseif ($f_type === 'image/png') $f_ext = 'png';
                    elseif ($f_type === 'image/webp') $f_ext = 'webp';
                    else $f_ext = 'png';
                }
                if ($f_err === UPLOAD_ERR_OK && in_array($f_ext, ['jpg', 'jpeg', 'png', 'webp']) && $f_size < 20000000) {
                    $new_name = uniqid('ad_', true) . '.' . $f_ext;
                    if (move_uploaded_file($f_tmp, $upload_dir . $new_name)) {
                        $is_primary = !$has_primary;
                        $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, ?)")->execute([$ad_id, $new_name, $is_primary ? 1 : 0]);
                        $has_primary = true;
                    }
                }
            }
        }
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}