<?php
// api/favorites.php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'login_required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$user_id = $_SESSION['user_id'];
$ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;

if (!$ad_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Ad ID']);
    exit;
}

try {
    // Check if already favorited
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND ad_id = ?");
    $stmt->execute([$user_id, $ad_id]);
    $fav = $stmt->fetch();

    if ($fav) {
        // Remove from favorites
        $del = $pdo->prepare("DELETE FROM favorites WHERE id = ?");
        $del->execute([$fav['id']]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to favorites
        $ins = $pdo->prepare("INSERT INTO favorites (user_id, ad_id) VALUES (?, ?)");
        $ins->execute([$user_id, $ad_id]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
