<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's message history
$stmt = $pdo->prepare("
    SELECT m.*, u.name as sender_name, a.title as ad_title 
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    LEFT JOIN ads a ON m.ad_id = a.id
    WHERE m.receiver_id = ? OR m.sender_id = ?
    ORDER BY m.created_at DESC
");
$stmt->execute([$user_id, $user_id]);
$messages = $stmt->fetchAll();

include 'includes/header.php'; 
?>

<style>
.messages-container {
    max-width: 1000px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}
.msg-card {
    padding: 20px;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
}
.msg-card.unread { border-left: 4px solid var(--accent-cyan); }
.msg-header { display: flex; justify-content: space-between; font-size: 14px; color: var(--text-secondary); }
.msg-body { font-size: 16px; color: var(--text-primary); }
</style>

<main>
    <div class="messages-container">
        <h2 style="color: var(--primary-teal);">Your Messages</h2>
        
        <?php if(count($messages) > 0): ?>
            <?php foreach($messages as $msg): ?>
                <div class="msg-card glass-panel <?= !$msg['is_read'] && $msg['receiver_id'] == $user_id ? 'unread' : '' ?>">
                    <div class="msg-header">
                        <strong><?= $msg['sender_id'] == $user_id ? 'You' : htmlspecialchars($msg['sender_name']) ?></strong>
                        <span>Re: <?= htmlspecialchars($msg['ad_title']) ?></span>
                        <span><?= date('M d, H:i', strtotime($msg['created_at'])) ?></span>
                    </div>
                    <div class="msg-body">
                        <?= htmlspecialchars($msg['content']) ?>
                    </div>
                    <!-- Quick Reply Form Wrapper -->
                    <?php if($msg['sender_id'] != $user_id): ?>
                    <form action="api/messages.php?action=send" method="POST" style="margin-top: 15px; display:flex; gap: 10px;">
                        <input type="hidden" name="receiver_id" value="<?= $msg['sender_id'] ?>">
                        <input type="hidden" name="ad_id" value="<?= $msg['ad_id'] ?>">
                        <input type="hidden" name="return_to" value="../messages.php">
                        <input type="text" name="content" placeholder="Type a reply..." style="flex:1; padding: 10px; border-radius: 8px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.5); outline: none;">
                        <button type="submit" class="btn-sell" style="padding: 10px 20px;">Reply</button>
                    </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="glass-panel" style="padding: 40px; text-align: center;">
                <i class="far fa-comments" style="font-size: 48px; color: var(--text-secondary); margin-bottom: 20px;"></i>
                <p>No messages yet. When you reply to an ad, your conversations will appear here.</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
