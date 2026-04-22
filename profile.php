<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$user_id]);
$user = $userStmt->fetch();

// Fetch user's ads  
$adsStmt = $pdo->prepare("
    SELECT a.*, c.name as category_name,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
    FROM ads a
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.user_id = ?
    ORDER BY a.created_at DESC
");
$adsStmt->execute([$user_id]);
$myAds = $adsStmt->fetchAll();

// Count stats
$totalAds = count($myAds);
$activeAds = count(array_filter($myAds, fn($a) => $a['status'] === 'active'));
$soldAds = count(array_filter($myAds, fn($a) => $a['status'] === 'sold'));

// Count messages
$msgStmt = $pdo->prepare("SELECT COUNT(DISTINCT IF(sender_id = ?, receiver_id, sender_id)) FROM messages WHERE sender_id = ? OR receiver_id = ?");
$msgStmt->execute([$user_id, $user_id, $user_id]);
$totalChats = $msgStmt->fetchColumn();

include 'includes/header.php'; 
?>

<style>
.profile-edit-form {
    display: none;
    padding: 30px;
    border-radius: var(--radius-xl);
    margin-bottom: 25px;
    animation: fadeInUp 0.3s ease;
}
.profile-edit-form.show { display: block; }
.profile-edit-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 18px;
}
.profile-edit-form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 600;
    font-size: 13px;
    color: var(--primary-teal);
}
.profile-edit-form input {
    width: 100%;
    padding: 12px 16px;
    border-radius: var(--radius-md);
    border: 1.5px solid var(--glass-border);
    background: rgba(255,255,255,0.5);
    font-size: 14px;
    font-family: inherit;
    outline: none;
    transition: all var(--transition-fast);
}
.profile-edit-form input:focus {
    border-color: var(--accent-cyan);
    box-shadow: 0 0 0 3px rgba(35, 229, 219, 0.15);
}

/* ad management card */
.my-ad-card {
    display: flex;
    gap: 18px;
    padding: 18px;
    border-radius: var(--radius-lg);
    margin-bottom: 15px;
    transition: all var(--transition-smooth);
    animation: fadeInUp 0.4s ease backwards;
}
.my-ad-card:hover {
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.my-ad-thumb {
    width: 140px;
    height: 105px;
    border-radius: var(--radius-md);
    object-fit: cover;
    flex-shrink: 0;
}
.my-ad-details { flex: 1; display: flex; flex-direction: column; justify-content: space-between; }
.my-ad-details h3 { font-size: 16px; font-weight: 600; color: var(--primary-teal); margin-bottom: 4px; }
.my-ad-details .price { font-size: 18px; font-weight: 700; color: var(--text-primary); }
.my-ad-meta { display: flex; align-items: center; gap: 15px; font-size: 12px; color: var(--text-secondary); margin-top: 6px; }

@media (max-width: 768px) {
    .my-ad-card { flex-direction: column; }
    .my-ad-thumb { width: 100%; height: 160px; }
    .profile-edit-form .form-row { grid-template-columns: 1fr; }
}
</style>

<main>
    <div class="profile-container">
        <!-- Profile Header -->
        <div class="profile-header glass-panel">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="profile-info" style="flex:1;">
                <h2><?= htmlspecialchars($user['name']) ?></h2>
                <p><i class="far fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                <?php if($user['phone']): ?>
                    <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user['phone']) ?></p>
                <?php endif; ?>
                <div class="profile-stats">
                    <div class="profile-stat">
                        <div class="stat-val"><?= $totalAds ?></div>
                        <div class="stat-label">Total Ads</div>
                    </div>
                    <div class="profile-stat">
                        <div class="stat-val"><?= $activeAds ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                    <div class="profile-stat">
                        <div class="stat-val"><?= $soldAds ?></div>
                        <div class="stat-label">Sold</div>
                    </div>
                    <div class="profile-stat">
                        <div class="stat-val"><?= $totalChats ?></div>
                        <div class="stat-label">Chats</div>
                    </div>
                </div>
            </div>
            <button class="btn-sell" style="align-self: flex-start;" onclick="toggleEditForm()">
                <i class="fas fa-pen"></i> Edit Profile
            </button>
        </div>

        <!-- Edit Profile Form (hidden by default) -->
        <div class="profile-edit-form glass-panel" id="editProfileForm">
            <h3 style="margin-bottom: 20px; color: var(--primary-teal);">Edit Profile</h3>
            <form action="api/auth.php?action=update_profile" method="POST">
                <div class="form-row">
                    <div>
                        <label>Full Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div>
                        <label>Phone Number</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="03xx-xxxxxxx">
                    </div>
                </div>
                <div class="form-row">
                    <div>
                        <label>Email (cannot be changed)</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="opacity:0.6;">
                    </div>
                    <div>
                        <label>New Password (leave blank to keep)</label>
                        <input type="password" name="new_password" placeholder="••••••••">
                    </div>
                </div>
                <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:10px;">
                    <button type="button" class="ad-action-btn" onclick="toggleEditForm()">Cancel</button>
                    <button type="submit" class="btn-sell" style="padding: 10px 24px;">Save Changes</button>
                </div>
            </form>
        </div>

        <!-- My Ads Section -->
        <div class="profile-tabs">
            <button class="profile-tab active" onclick="filterAds('all', this)">All Ads (<?= $totalAds ?>)</button>
            <button class="profile-tab" onclick="filterAds('active', this)">Active (<?= $activeAds ?>)</button>
            <button class="profile-tab" onclick="filterAds('sold', this)">Sold (<?= $soldAds ?>)</button>
        </div>

        <div id="adsContainer">
            <?php if(count($myAds) > 0): ?>
                <?php foreach($myAds as $i => $ad): ?>
                    <div class="my-ad-card glass-panel" data-status="<?= $ad['status'] ?>" style="animation-delay: <?= $i * 0.06 ?>s">
                        <a href="ad.php?id=<?= $ad['id'] ?>">
                            <?php $img = $ad['main_image'] ?: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=300&q=60'; ?>
                            <img src="<?= htmlspecialchars($img) ?>" alt="Ad" class="my-ad-thumb">
                        </a>
                        <div class="my-ad-details">
                            <div>
                                <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                                    <h3><?= htmlspecialchars($ad['title']) ?></h3>
                                    <span class="status-badge status-<?= $ad['status'] ?>"><?= ucfirst($ad['status']) ?></span>
                                </div>
                                <div class="price">Rs <?= number_format($ad['price']) ?></div>
                                <div class="my-ad-meta">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?></span>
                                    <span><i class="far fa-clock"></i> <?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                                    <?php if($ad['category_name']): ?>
                                        <span><i class="fas fa-tag"></i> <?= htmlspecialchars($ad['category_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="ad-actions">
                                <?php if($ad['status'] === 'active'): ?>
                                    <button class="ad-action-btn btn-sold" onclick="confirmAction('Mark this ad as sold?', () => window.location='api/ads.php?action=mark_sold&id=<?= $ad['id'] ?>')">
                                        <i class="fas fa-check-circle"></i> Mark Sold
                                    </button>
                                <?php endif; ?>
                                <button class="ad-action-btn btn-delete" onclick="confirmAction('Are you sure you want to delete this ad? This cannot be undone.', () => window.location='api/ads.php?action=delete&id=<?= $ad['id'] ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="glass-panel" style="padding: 60px; text-align: center; border-radius: var(--radius-xl);">
                    <i class="fas fa-box-open" style="font-size: 56px; color: var(--glass-border); margin-bottom: 20px;"></i>
                    <h3>No Ads Posted Yet</h3>
                    <p style="color: var(--text-secondary); margin: 10px 0 25px;">Start selling by posting your first ad!</p>
                    <a href="post-ad.php" class="btn-sell"><i class="fas fa-plus"></i> Post an Ad</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function toggleEditForm() {
    $('#editProfileForm').toggleClass('show');
}

function filterAds(status, btn) {
    $('.profile-tab').removeClass('active');
    $(btn).addClass('active');
    
    if (status === 'all') {
        $('.my-ad-card').show();
    } else {
        $('.my-ad-card').hide();
        $(`.my-ad-card[data-status="${status}"]`).show();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
