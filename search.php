<?php
require_once 'includes/config.php';

$query = isset($_GET['q']) ? sanitize_input($_GET['q']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$location = isset($_GET['location']) ? sanitize_input($_GET['location']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'time_desc';

// Build the search query dynamically
$sql = "
    SELECT a.id, a.title, a.price, a.location, a.created_at, a.condition_type, c.name as category_name,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
    FROM ads a
    JOIN categories c ON a.category_id = c.id
    WHERE a.status = 'active'
";
$params = [];

if (!empty($query)) {
    $sql .= " AND (a.title LIKE ? OR a.description LIKE ?)";
    $params[] = "%$query%";
    $params[] = "%$query%";
}

if (!empty($category) && $category !== 'all') {
    $sql .= " AND c.slug = ?";
    $params[] = $category;
}

if (!empty($location)) {
    $sql .= " AND a.location LIKE ?";
    $params[] = "%$location%";
}

if ($sort === 'price_asc') {
    $sql .= " ORDER BY a.price ASC LIMIT 50";
} elseif ($sort === 'price_desc') {
    $sql .= " ORDER BY a.price DESC LIMIT 50";
} else {
    $sql .= " ORDER BY a.created_at DESC LIMIT 50";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

// Fetch category list for filter
$catStmt = $pdo->query("SELECT slug, name FROM categories ORDER BY name");
$categories = $catStmt->fetchAll();

include 'includes/header.php'; 
?>

<style>
.search-header-panel {
    margin-bottom: 25px;
    padding: 25px;
    border-radius: var(--radius-xl);
    animation: fadeInUp 0.3s ease;
}

.search-header-panel h2 {
    font-size: 22px;
    font-weight: 700;
    color: var(--primary-teal);
    margin-bottom: 4px;
}

.search-header-panel p {
    color: var(--text-secondary);
    font-size: 14px;
    margin-bottom: 18px;
}

.filter-form {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    align-items: center;
}

.filter-form input,
.filter-form select {
    padding: 10px 16px;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--glass-border);
    background: rgba(255,255,255,0.6);
    font-family: inherit;
    font-size: 14px;
    outline: none;
    transition: all var(--transition-fast);
    color: var(--text-primary);
}

.filter-form input:focus,
.filter-form select:focus {
    border-color: var(--accent-cyan);
    box-shadow: 0 0 0 3px rgba(35, 229, 219, 0.12);
}

.active-filters {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.filter-tag {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: var(--radius-full);
    background: rgba(35, 229, 219, 0.1);
    color: var(--primary-teal);
    font-size: 12px;
    font-weight: 600;
}

.filter-tag a {
    color: var(--danger);
    font-size: 14px;
}

/* Category pills for search page */
.category-filter-strip {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.cat-pill {
    padding: 6px 14px;
    border-radius: var(--radius-full);
    background: rgba(255,255,255,0.5);
    border: 1px solid var(--glass-border);
    font-size: 13px;
    font-weight: 500;
    color: var(--text-secondary);
    transition: all var(--transition-fast);
    text-decoration: none;
}

.cat-pill:hover,
.cat-pill.active {
    background: var(--primary-teal);
    color: white;
    border-color: var(--primary-teal);
}
</style>

<main>
    <div class="search-header-panel glass-panel">
        <h2>
            <?php if($query): ?>
                Results for "<?= htmlspecialchars($query) ?>"
            <?php elseif($category): ?>
                <?= ucfirst(htmlspecialchars($category)) ?>
            <?php else: ?>
                Browse All Ads
            <?php endif; ?>
        </h2>
        <p><?= count($results) ?> ad<?= count($results) !== 1 ? 's' : '' ?> found</p>
        
        <form action="search.php" method="GET" class="filter-form" id="searchFilterForm">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search keyword..." style="flex:1; min-width:180px;">
            <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="City or area..." style="min-width:150px;">
            
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['slug'] ?>" <?= $category == $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="sort">
                <option value="time_desc" <?= $sort == 'time_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Lowest Price</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Highest Price</option>
            </select>
            
            <button type="submit" class="btn-sell" style="padding: 10px 22px;">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>

        <?php if($query || $category || $location): ?>
        <div class="active-filters">
            <?php if($query): ?>
                <span class="filter-tag"><i class="fas fa-search"></i> <?= htmlspecialchars($query) ?> <a href="search.php?category=<?= $category ?>&location=<?= $location ?>&sort=<?= $sort ?>">&times;</a></span>
            <?php endif; ?>
            <?php if($category): ?>
                <span class="filter-tag"><i class="fas fa-tag"></i> <?= ucfirst(htmlspecialchars($category)) ?> <a href="search.php?q=<?= $query ?>&location=<?= $location ?>&sort=<?= $sort ?>">&times;</a></span>
            <?php endif; ?>
            <?php if($location): ?>
                <span class="filter-tag"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($location) ?> <a href="search.php?q=<?= $query ?>&category=<?= $category ?>&sort=<?= $sort ?>">&times;</a></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="product-grid">
        <?php if (count($results) > 0): ?>
            <?php foreach($results as $ad): ?>
                <a href="ad.php?id=<?= $ad['id'] ?>" class="product-card glass-panel" style="display:block; color:inherit; text-decoration:none;">
                    <button class="favorite-btn" onclick="event.preventDefault();"><i class="far fa-heart"></i></button>
                    <span class="glass-pill" style="position: absolute; top: 10px; left: 10px; padding: 5px 12px; font-size: 11px; z-index: 10;"><?= htmlspecialchars($ad['category_name']) ?></span>
                    <?php $img = $ad['main_image'] ? $ad['main_image'] : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=60'; ?>
                    <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="product-img">
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
            <div style="grid-column: 1/-1; text-align: center; padding: 80px 30px; animation: fadeInUp 0.5s ease;">
                <i class="fas fa-search" style="font-size: 56px; color: var(--glass-border); margin-bottom: 20px;"></i>
                <h3 style="margin-bottom:8px;">No results found</h3>
                <p style="color: var(--text-secondary); margin-bottom:25px;">Try broadening your search or removing some filters.</p>
                <a href="search.php" class="btn-sell"><i class="fas fa-redo"></i> Clear Filters</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
