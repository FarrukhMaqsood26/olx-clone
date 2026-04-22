<?php
require_once 'includes/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch ad details
$stmt = $pdo->prepare("
    SELECT a.*, u.name as seller_name, u.avatar, u.phone, u.created_at as member_since, c.name as category_name
    FROM ads a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.id = ?
");
$stmt->execute([$id]);
$ad = $stmt->fetch();

if (!$ad) {
    include 'includes/header.php';
    echo '<main><div class="glass-panel" style="max-width:600px; margin:80px auto; padding:60px; text-align:center; border-radius:var(--radius-xl);">
        <i class="fas fa-search" style="font-size:56px; color:var(--glass-border); margin-bottom:20px;"></i>
        <h2 style="margin-bottom:10px;">Ad Not Found</h2>
        <p style="color:var(--text-secondary); margin-bottom:25px;">This ad may have been removed or is no longer available.</p>
        <a href="index.php" class="btn-sell"><i class="fas fa-home"></i> Back to Home</a>
    </div></main>';
    include 'includes/footer.php';
    exit;
}

// Fetch ad images
$imgStmt = $pdo->prepare("SELECT image_path FROM ad_images WHERE ad_id = ? ORDER BY is_primary DESC");
$imgStmt->execute([$id]);
$images = $imgStmt->fetchAll();

// Fetch similar ads
$similarStmt = $pdo->prepare("
    SELECT a.id, a.title, a.price, a.location,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
    FROM ads a
    WHERE a.category_id = ? AND a.id != ? AND a.status = 'active'
    ORDER BY RAND() LIMIT 4
");
$similarStmt->execute([$ad['category_id'], $id]);
$similarAds = $similarStmt->fetchAll();

include 'includes/header.php'; 
?>

<style>
.ad-detail-container {
    max-width: 1200px;
    margin: 30px auto;
    display: grid;
    grid-template-columns: 1.6fr 1fr;
    gap: 25px;
    animation: fadeInUp 0.5s ease;
}

.ad-gallery {
    position: relative;
    border-radius: var(--radius-lg);
    overflow: hidden;
}

.ad-gallery img {
    width: 100%;
    border-radius: var(--radius-lg);
    object-fit: cover;
    max-height: 480px;
    transition: transform 0.5s ease;
    cursor: zoom-in;
}

.ad-gallery:hover img {
    transform: scale(1.02);
}

.ad-thumb-strip {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.ad-thumb-strip img {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: var(--radius-sm);
    cursor: pointer;
    border: 2px solid transparent;
    transition: all var(--transition-fast);
    opacity: 0.7;
}

.ad-thumb-strip img:hover,
.ad-thumb-strip img.active {
    border-color: var(--accent-cyan);
    opacity: 1;
}

.ad-info h1 {
    color: var(--primary-teal);
    margin-bottom: 8px;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.3;
}

.ad-price-tag {
    font-size: 34px;
    font-weight: 800;
    margin-bottom: 16px;
    background: linear-gradient(135deg, var(--primary-teal), #004d56);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.ad-meta-row {
    display: flex;
    gap: 20px;
    margin-bottom: 12px;
    font-size: 14px;
    color: var(--text-secondary);
}

.ad-meta-row i {
    color: var(--accent-cyan);
    width: 18px;
    text-align: center;
    margin-right: 5px;
}

.seller-card {
    margin-top: 25px;
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px;
    background: rgba(255,255,255,0.4);
    border-radius: var(--radius-md);
    border: 1px solid var(--glass-border);
}

.seller-avatar {
    width: 54px;
    height: 54px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent-cyan), var(--accent-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: white;
    flex-shrink: 0;
}

.sold-overlay {
    position: absolute;
    top: 20px;
    left: 20px;
    background: rgba(139, 92, 246, 0.9);
    color: white;
    padding: 8px 20px;
    border-radius: var(--radius-full);
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 1px;
    z-index: 5;
}

.desc-section {
    padding: 28px;
    margin-top: 25px;
    border-radius: var(--radius-xl);
}

.desc-section h3 {
    margin-bottom: 15px;
    color: var(--primary-teal);
    font-size: 18px;
    font-weight: 700;
}

.desc-section p {
    color: var(--text-secondary);
    line-height: 1.7;
    white-space: pre-wrap;
    font-size: 15px;
}

/* Similar ads section */
.similar-section {
    margin-top: 50px;
    grid-column: 1 / -1;
}
</style>

<main>
    <div class="ad-detail-container">
        <!-- Left: Images and Details -->
        <div class="ad-left">
            <div class="glass-panel" style="padding: 20px; border-radius: var(--radius-xl);">
                <div class="ad-gallery">
                    <?php if($ad['status'] === 'sold'): ?>
                        <div class="sold-overlay">SOLD</div>
                    <?php endif; ?>
                    <?php if (count($images) > 0): ?>
                        <img src="<?= htmlspecialchars($images[0]['image_path']) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" id="mainAdImage">
                    <?php else: ?>
                        <div style="height:350px; background:linear-gradient(135deg, #f8fafc, #e2e8f0); border-radius:var(--radius-lg); display:flex; align-items:center; justify-content:center; color:#94a3b8;">
                            <div style="text-align:center;">
                                <i class="fas fa-image" style="font-size:48px; margin-bottom:10px;"></i>
                                <p>No Image Available</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php if(count($images) > 1): ?>
                <div class="ad-thumb-strip">
                    <?php foreach($images as $i => $img): ?>
                        <img src="<?= htmlspecialchars($img['image_path']) ?>" 
                             alt="Thumbnail <?= $i+1 ?>"
                             class="<?= $i === 0 ? 'active' : '' ?>"
                             onclick="changeMainImage(this, '<?= htmlspecialchars($img['image_path']) ?>')">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="desc-section glass-panel">
                <h3><i class="fas fa-align-left" style="margin-right:8px; color:var(--accent-cyan);"></i> Description</h3>
                <p><?= nl2br(htmlspecialchars($ad['description'])) ?></p>
            </div>
            
            <div class="desc-section glass-panel" style="margin-top:15px;">
                <h3><i class="fas fa-info-circle" style="margin-right:8px; color:var(--accent-cyan);"></i> Details</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px;">
                    <div style="padding:12px; background:rgba(255,255,255,0.4); border-radius:var(--radius-sm);">
                        <span style="font-size:12px; color:var(--text-secondary); display:block;">Condition</span>
                        <span style="font-weight:600; text-transform:capitalize;"><?= $ad['condition_type'] ?></span>
                    </div>
                    <?php if($ad['category_name']): ?>
                    <div style="padding:12px; background:rgba(255,255,255,0.4); border-radius:var(--radius-sm);">
                        <span style="font-size:12px; color:var(--text-secondary); display:block;">Category</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($ad['category_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div style="padding:12px; background:rgba(255,255,255,0.4); border-radius:var(--radius-sm);">
                        <span style="font-size:12px; color:var(--text-secondary); display:block;">Location</span>
                        <span style="font-weight:600;"><?= htmlspecialchars($ad['location']) ?></span>
                    </div>
                    <div style="padding:12px; background:rgba(255,255,255,0.4); border-radius:var(--radius-sm);">
                        <span style="font-size:12px; color:var(--text-secondary); display:block;">Posted</span>
                        <span style="font-weight:600;"><?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Price and Seller Info -->
        <div class="ad-right">
            <div class="glass-panel" style="padding: 28px; position: sticky; top: 90px; border-radius: var(--radius-xl);">
                <div class="ad-price-tag">Rs <?= number_format($ad['price']) ?></div>
                <h1><?= htmlspecialchars($ad['title']) ?></h1>
                
                <div class="ad-meta-row" style="margin-top:15px;">
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?></span>
                    <span><i class="far fa-clock"></i> <?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 20px 0;">
                
                <div class="seller-card">
                    <div class="seller-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 17px;"><?= htmlspecialchars(explode(' ', trim($ad['seller_name']))[0]) ?></div>
                        <div style="font-size: 13px; color: var(--text-secondary);">Member since <?= date('M Y', strtotime($ad['member_since'])) ?></div>
                    </div>
                </div>

                <div style="margin-top: 25px; display: flex; flex-direction: column; gap: 12px;">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_id'] != $ad['user_id']): ?>
                            <a href="chat.php?partner_id=<?= $ad['user_id'] ?>" class="btn-sell" style="width: 100%; border-radius: var(--radius-md); text-decoration:none; display:flex; align-items:center; justify-content:center; gap:10px; padding: 14px;" id="msg-seller-btn">
                                <i class="far fa-envelope"></i> Message Seller
                            </a>
                        <?php else: ?>
                            <div style="background: rgba(35, 229, 219, 0.08); padding: 16px; border-radius: var(--radius-md); text-align:center; color: var(--primary-teal); font-weight:500;">
                                <i class="fas fa-info-circle"></i> This is your ad
                            </div>
                            <a href="profile.php" class="ad-action-btn btn-edit" style="justify-content:center; padding:12px; font-size:14px; text-decoration:none;">
                                <i class="fas fa-cog"></i> Manage in Profile
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn-sell" style="width: 100%; margin:0; text-decoration: none; display:flex; text-align:center; justify-content:center; gap:8px; border-radius: var(--radius-md); padding:14px;">
                            <i class="far fa-envelope"></i> Login to Message
                        </a>
                    <?php endif; ?>
                    
                    <?php if($ad['phone']): ?>
                    <div style="background: rgba(255,255,255,0.6); border: 1px solid var(--glass-border); padding: 14px 18px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between;">
                        <div style="display:flex; align-items:center; gap: 10px; font-weight:600; font-size:16px;">
                            <i class="fas fa-phone" style="color:var(--success);"></i> 
                            <span><?= htmlspecialchars($ad['phone']) ?></span>
                        </div>
                        <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($ad['phone']) ?>'); showToast('Number copied!', 'success');" style="background:none; border:none; color:var(--accent-blue); cursor:pointer; padding:5px; font-weight:600; font-family:inherit;" title="Copy Number">
                            <i class="far fa-copy"></i> Copy
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <button class="favorite-btn" style="position:relative; width:100%; height:auto; border-radius:var(--radius-md); padding:12px; display:flex; align-items:center; justify-content:center; gap:8px; background:rgba(255,255,255,0.5); border:1px solid var(--glass-border); font-family:inherit; font-size:14px; font-weight:600;">
                        <i class="far fa-heart"></i> Add to Favorites
                    </button>
                </div>
            </div>
        </div>

        <!-- Similar Ads -->
        <?php if(count($similarAds) > 0): ?>
        <div class="similar-section">
            <div class="section-header">
                <h2>Similar Ads</h2>
            </div>
            <div class="product-grid">
                <?php foreach($similarAds as $sim): ?>
                    <a href="ad.php?id=<?= $sim['id'] ?>" class="product-card glass-panel" style="display:block; color:inherit; text-decoration:none;">
                        <button class="favorite-btn" onclick="event.preventDefault();"><i class="far fa-heart"></i></button>
                        <?php $img = $sim['main_image'] ?: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=60'; ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="Ad" class="product-img">
                        <div class="product-info">
                            <div class="product-price">Rs <?= number_format($sim['price']) ?></div>
                            <div class="product-title"><?= htmlspecialchars($sim['title']) ?></div>
                            <div class="product-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($sim['location']) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
function changeMainImage(thumb, src) {
    $('#mainAdImage').attr('src', src);
    $('.ad-thumb-strip img').removeClass('active');
    $(thumb).addClass('active');
}
</script>

<?php include 'includes/footer.php'; ?>
