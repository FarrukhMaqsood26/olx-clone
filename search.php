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

<main class="flex-grow py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-1">
            <?php if($query): ?>
                Results for "<?= htmlspecialchars($query) ?>"
            <?php elseif($category): ?>
                <?= ucfirst(htmlspecialchars($category)) ?>
            <?php else: ?>
                Browse All Ads
            <?php endif; ?>
        </h2>
        <p class="text-slate-500 text-sm mb-6"><?= count($results) ?> ad<?= count($results) !== 1 ? 's' : '' ?> found</p>
        
        <form action="search.php" method="GET" class="flex flex-wrap gap-4 items-center">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Search keyword..." class="flex-grow min-w-[200px] px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800">
            <input type="text" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="City or area..." class="w-full sm:w-auto min-w-[150px] px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800">
            
            <select name="category" class="w-full sm:w-auto px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['slug'] ?>" <?= $category == $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select name="sort" class="w-full sm:w-auto px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800 bg-white">
                <option value="time_desc" <?= $sort == 'time_desc' ? 'selected' : '' ?>>Newest First</option>
                <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Lowest Price</option>
                <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Highest Price</option>
            </select>
            
            <button type="submit" class="w-full sm:w-auto bg-brand hover:bg-brand-light text-white font-bold py-3 px-6 rounded-lg transition sm:ml-auto">
                <i class="fas fa-filter"></i> Filter
            </button>
        </form>

        <?php if($query || $category || $location): ?>
        <div class="flex flex-wrap gap-2 mt-6 pt-6 border-t border-slate-100">
            <?php if($query): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand/10 text-brand text-xs font-bold">
                    <i class="fas fa-search"></i> <?= htmlspecialchars($query) ?> 
                    <a href="search.php?category=<?= $category ?>&location=<?= $location ?>&sort=<?= $sort ?>" class="text-red-500 hover:text-red-700 ml-1 text-sm">&times;</a>
                </span>
            <?php endif; ?>
            <?php if($category): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand/10 text-brand text-xs font-bold">
                    <i class="fas fa-tag"></i> <?= ucfirst(htmlspecialchars($category)) ?> 
                    <a href="search.php?q=<?= $query ?>&location=<?= $location ?>&sort=<?= $sort ?>" class="text-red-500 hover:text-red-700 ml-1 text-sm">&times;</a>
                </span>
            <?php endif; ?>
            <?php if($location): ?>
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand/10 text-brand text-xs font-bold">
                    <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($location) ?> 
                    <a href="search.php?q=<?= $query ?>&category=<?= $category ?>&sort=<?= $sort ?>" class="text-red-500 hover:text-red-700 ml-1 text-sm">&times;</a>
                </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (count($results) > 0): ?>
            <?php foreach($results as $ad): ?>
                <a href="ad.php?id=<?= $ad['id'] ?>" class="group bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col relative block">
                    <button class="absolute top-3 right-3 bg-white/90 backdrop-blur text-slate-400 w-9 h-9 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 z-10 transition">
                        <i class="far fa-heart text-lg"></i>
                    </button>
                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur font-bold text-slate-700 px-3 py-1 rounded-full text-[10px] uppercase tracking-wider z-10 shadow-sm border border-slate-100">
                        <?= htmlspecialchars($ad['category_name']) ?>
                    </span>
                    
                    <?php $img = $ad['main_image'] ? $ad['main_image'] : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=60'; ?>
                    <div class="aspect-[4/3] overflow-hidden border-b border-slate-100">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <div class="text-xl font-bold text-slate-900 mb-1">Rs <?= number_format($ad['price']) ?></div>
                        <div class="text-sm text-slate-600 line-clamp-2 mb-4 flex-1 leading-snug"><?= htmlspecialchars($ad['title']) ?></div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wide flex justify-between items-center mt-auto">
                            <span class="flex items-center gap-1 truncate max-w-[65%]"><i class="fas fa-map-marker-alt text-brand"></i> <?= htmlspecialchars($ad['location']) ?></span>
                            <span><?= date('M d', strtotime($ad['created_at'])) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 bg-white border border-slate-200 border-dashed rounded-2xl p-16 text-center text-slate-500 flex flex-col items-center justify-center">
                <i class="fas fa-search text-5xl mb-4 text-slate-300"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No results found</h3>
                <p class="mb-6">Try broadening your search or removing some filters.</p>
                <a href="search.php" class="bg-white border border-slate-300 text-slate-700 font-bold px-6 py-2.5 rounded-full hover:bg-slate-50 transition flex items-center gap-2">
                    <i class="fas fa-redo"></i> Clear Filters
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
