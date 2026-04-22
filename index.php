<?php
require_once 'includes/config.php';

// Fetch latest ads
$stmt = $pdo->query("
    SELECT a.id, a.title, a.price, a.location, a.created_at, 
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
    FROM ads a
    WHERE a.status = 'active'
    ORDER BY a.created_at DESC
    LIMIT 8
");
$ads = $stmt->fetchAll();

include 'includes/header.php'; 
?>

<main>
    <!-- Category Strip -->
    <div class="category-strip">
        <a href="search.php" class="glass-pill"><i class="fas fa-th-large"></i> ALL</a>
        <a href="search.php?category=mobiles" class="glass-pill"><i class="fas fa-mobile-alt"></i> Mobiles</a>
        <a href="search.php?category=vehicles" class="glass-pill"><i class="fas fa-car"></i> Vehicles</a>
        <a href="search.php?category=property" class="glass-pill"><i class="fas fa-home"></i> Property</a>
        <a href="search.php?category=electronics" class="glass-pill"><i class="fas fa-tv"></i> Electronics</a>
        <a href="search.php?category=bikes" class="glass-pill"><i class="fas fa-motorcycle"></i> Bikes</a>
        <a href="search.php?category=jobs" class="glass-pill"><i class="fas fa-briefcase"></i> Jobs</a>
        <a href="search.php?category=furniture" class="glass-pill"><i class="fas fa-couch"></i> Furniture</a>
        <a href="search.php?category=animals" class="glass-pill"><i class="fas fa-paw"></i> Animals</a>
    </div>

    <!-- Hero Banner -->
    <section class="hero glass-panel">
        <h1>Discover Amazing Deals</h1>
        <p>Buy & sell anything near you with safety and ease.</p>
        <div style="margin-top: 25px; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="post-ad.php" class="btn-sell" style="text-decoration:none;"><i class="fas fa-plus"></i> Start Selling</a>
            <a href="search.php" class="glass-pill" style="padding: 12px 24px; font-weight: 600; text-decoration:none;"><i class="fas fa-compass"></i> Explore All</a>
        </div>
    </section>

    <!-- Product Grid: Fresh Recommendations -->
    <section>
        <div class="section-header">
            <h2>Fresh recommendations</h2>
            <a href="search.php" style="color:var(--accent-blue); font-weight:600; font-size:14px; display:flex; align-items:center; gap:5px;">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="product-grid">
            <?php if (count($ads) > 0): ?>
                <?php foreach($ads as $ad): ?>
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="product-card glass-panel" style="display:block; color:inherit; text-decoration:none;">
                        <button class="favorite-btn" onclick="event.preventDefault();"><i class="far fa-heart"></i></button>
                        <?php 
                            $img = $ad['main_image'] ? $ad['main_image'] : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=60'; 
                        ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="product-img" loading="lazy">
                        <div class="product-info">
                            <div class="product-price">Rs <?= number_format($ad['price']) ?></div>
                            <div class="product-title"><?= htmlspecialchars($ad['title']) ?></div>
                            <div class="product-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($ad['location']) ?></span>
                                <span><?= date('M d', strtotime($ad['created_at'])) ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-secondary); animation: fadeInUp 0.5s ease;">
                    <i class="fas fa-box-open" style="font-size: 56px; margin-bottom: 20px; color: var(--glass-border);"></i>
                    <h3 style="margin-bottom:8px;">No ads found</h3>
                    <p>Be the first one to post a deal in your area!</p>
                    <a href="post-ad.php" class="btn-sell" style="margin-top:20px; text-decoration:none; display:inline-flex;"><i class="fas fa-plus"></i> Post an Ad</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
