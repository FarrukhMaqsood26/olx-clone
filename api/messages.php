<?php
// api/messages.php
require_once '../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Unauthorized']));
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id = $_SESSION['user_id'];

function ensure_typing_table($pdo) {
    static $ensured = false;
    if ($ensured) {
        return;
    }
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS chat_typing_status (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            partner_id INT NOT NULL,
            is_typing TINYINT(1) NOT NULL DEFAULT 0,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_partner (user_id, partner_id),
            INDEX idx_partner_user (partner_id, user_id),
            INDEX idx_updated_at (updated_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    $ensured = true;
}

// 1. Send Message (Supports Text, Image, Audio)
if ($action == 'send' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $receiver_id = intval($_POST['receiver_id']);
    $ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : null;
    $content = isset($_POST['content']) ? sanitize_input($_POST['content']) : '';
    
    $file_path = null;
    $file_type = 'text';

    // Handle File Uploads (Image or Audio)
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/uploads/chat/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $orig_name = basename($_FILES['attachment']['name']);
        $file_ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));
        
        // If no extension (blobs), try to guess from mime type
        if (empty($file_ext)) {
            $mime = $_FILES['attachment']['type'];
            if (strpos($mime, 'image/') === 0) $file_ext = str_replace('image/', '', $mime);
            elseif (strpos($mime, 'audio/') === 0) {
                $file_ext = 'webm'; // default for browser recordings
                if ($mime == 'audio/ogg') $file_ext = 'ogg';
                if ($mime == 'audio/mpeg') $file_ext = 'mp3';
            }
        }

        $file_name = time() . '_' . uniqid() . '.' . $file_ext;
        $target_file = $upload_dir . $file_name;

        // Determine file type category
        $image_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $audio_exts = ['mp3', 'wav', 'ogg', 'm4a', 'webm', 'mp4', 'aac'];

        if (in_array($file_ext, $image_exts)) {
            $file_type = 'image';
        } elseif (in_array($file_ext, $audio_exts)) {
            $file_type = 'audio';
        } else {
            $file_type = 'file';
        }

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
            $file_path = 'assets/uploads/chat/' . $file_name;
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

// Typing status: set current user's typing state for partner
if ($action == 'set_typing' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    ensure_typing_table($pdo);
    $partner_id = intval($_POST['partner_id'] ?? 0);
    $is_typing = intval($_POST['is_typing'] ?? 0) ? 1 : 0;

    if ($partner_id <= 0 || $partner_id === (int)$user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid partner']);
        exit;
    }

    $stmt = $pdo->prepare("
        INSERT INTO chat_typing_status (user_id, partner_id, is_typing, updated_at)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE is_typing = VALUES(is_typing), updated_at = NOW()
    ");
    $stmt->execute([$user_id, $partner_id, $is_typing]);
    echo json_encode(['status' => 'success']);
    exit;
}

// Typing status: check if partner is currently typing to current user
if ($action == 'get_typing' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    ensure_typing_table($pdo);
    $partner_id = intval($_GET['partner_id'] ?? 0);

    if ($partner_id <= 0 || $partner_id === (int)$user_id) {
        echo json_encode(['status' => 'success', 'is_typing' => 0]);
        exit;
    }

    // Consider typing "active" only if refreshed in the last ~10 seconds
    $stmt = $pdo->prepare("
        SELECT is_typing, updated_at
        FROM chat_typing_status
        WHERE user_id = ? AND partner_id = ?
        LIMIT 1
    ");
    $stmt->execute([$partner_id, $user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $isTyping = 0;
    if ($row && (int)$row['is_typing'] === 1) {
        $isFresh = (strtotime($row['updated_at']) >= (time() - 10));
        $isTyping = $isFresh ? 1 : 0;
    }

    echo json_encode(['status' => 'success', 'is_typing' => $isTyping]);
    exit;
}

// Check if partner is currently online (active in last 30 seconds)
if ($action == 'get_online_status' && $_SERVER['REQUEST_METHOD'] == 'GET') {
    $partner_id = intval($_GET['partner_id'] ?? 0);

    if ($partner_id <= 0) {
        echo json_encode(['status' => 'success', 'is_online' => 0]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT last_activity FROM users WHERE id = ?");
    $stmt->execute([$partner_id]);
    $last_activity = $stmt->fetchColumn();

    $is_online = 0;
    if ($last_activity) {
        $isFresh = (strtotime($last_activity) >= (time() - 30));
        $is_online = $isFresh ? 1 : 0;
    }

    echo json_encode(['status' => 'success', 'is_online' => $is_online]);
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

// 5. Delete Message
if ($action == 'delete' && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $msg_id = intval($_POST['message_id']);
    
    $check = $pdo->prepare("SELECT sender_id FROM messages WHERE id = ?");
    $check->execute([$msg_id]);
    $msg = $check->fetch();
    
    if ($msg && $msg['sender_id'] == $user_id) {
        $del = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        if ($del->execute([$msg_id])) {
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized or error']);
    exit;
}
?>
