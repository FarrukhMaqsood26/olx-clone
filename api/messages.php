<?php
// api/messages.php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

// 1. Send Message (Supports Text, Image, Audio)
if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = intval($_POST['receiver_id']);
    $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : null;
    $content = isset($_POST['content']) ? sanitize_input($_POST['content']) : '';
    
    $file_path = null;
    $file_type = 'text';

    // Handle File Uploads (Image or Audio)
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/chat/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        $file_name = time() . '_' . basename($_FILES['attachment']['name']);
        $target_file = $upload_dir . $file_name;
        $file_ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Determine file type
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'webm'];

        if (in_array($file_ext, $image_exts)) {
            $file_type = 'image';
        } elseif (in_array($file_ext, $audio_exts)) {
            $file_type = 'audio';
        }

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $file_path = 'uploads/chat/' . $file_name;
        }
    }

    if (empty($content) && empty($file_path)) {
        die(json_encode(['status' => 'error', 'message' => 'Empty message.']));
    }
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, ad_id, content, file_path, file_type) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt->execute([$user_id, $receiver_id, $ad_id, $content, $file_path, $file_type])) {
        if (isset($_POST['is_ajax'])) {
            echo json_encode(['status' => 'success', 'message_id' => $pdo->lastInsertId()]);
        } else {
            $return = isset($_POST['return_to']) ? $_POST['return_to'] : '../chat.php';
            header("Location: " . $return);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'DB error.']);
    }
    exit;
}

// 2. Fetch Conversation List (Sidebar)
if ($action == 'list_conversations') {
    $stmt = $pdo->prepare("
        SELECT 
            u.id as partner_id, u.name as partner_name, u.avatar,
            m.content as last_msg, m.created_at as last_time, m.file_type, m.sender_id
        FROM users u
        INNER JOIN (
            SELECT 
                IF(sender_id = ?, receiver_id, sender_id) as partner_id,
                MAX(id) as max_id
            FROM messages
            WHERE sender_id = ? OR receiver_id = ?
            GROUP BY partner_id
        ) as conversation_meta ON u.id = conversation_meta.partner_id
        INNER JOIN messages m ON m.id = conversation_meta.max_id
        ORDER BY m.created_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// 3. Fetch Full Thread with a User
if ($action == 'get_thread') {
    $partner_id = intval($_GET['partner_id']);
    
    // Mark messages as read
    $upStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
    $upStmt->execute([$partner_id, $user_id]);

    $stmt = $pdo->prepare("
        SELECT m.*, u.name as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $partner_id, $partner_id, $user_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// 4. Poll for New Messages in active thread
if ($action == 'poll') {
    $partner_id = intval($_GET['partner_id']);
    $last_id = intval($_GET['last_id']);

    $stmt = $pdo->prepare("
        SELECT m.*, u.name as sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?) 
           OR (m.sender_id = ? AND m.receiver_id = ?))
           AND m.id > ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$user_id, $partner_id, $partner_id, $user_id, $last_id]);
    echo json_encode($stmt->fetchAll());
    exit;
}
?>
