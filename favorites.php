<?php
require_once 'includes/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=login_required");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's favorite ads
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name,
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image
    FROM favorites f
    JOIN ads a ON f.ad_id = a.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
");
$stmt->execute([$user_id]);
$favAds = $stmt->fetchAll();

include 'includes/header.php';
?>


    <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900 mb-2">My Favorites</h1>
        <p class="text-slate-500 font-medium italic">Your saved items for quick access.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        <?php if (count($favAds) > 0): ?>
            <?php foreach($favAds as $ad): ?>
                <div class="group bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-xl transition duration-300 flex flex-col relative">
                    <button onclick="toggleFavorite(<?= $ad['id'] ?>, this); $(this).closest('.group').fadeOut();" class="absolute top-3 right-3 bg-white/90 backdrop-blur text-red-500 w-9 h-9 rounded-full flex items-center justify-center shadow-sm z-10 transition hover:bg-white">
                        <i class="fas fa-heart text-lg"></i>
                    </button>
                    
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="block flex-1">
                        <?php $img = get_ad_image($ad['main_image']); ?>
                        <div class="aspect-[4/3] overflow-hidden border-b border-slate-100">
                            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                        </div>
                        <div class="p-4 flex flex-col">
                            <div class="text-xl font-bold text-slate-900 mb-1">Rs <?= number_format($ad['price']) ?></div>
                            <div class="text-sm text-slate-600 line-clamp-2 mb-4 leading-snug"><?= htmlspecialchars($ad['title']) ?></div>
                            <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wide flex justify-between items-center mt-auto">
                                <span class="flex items-center gap-1 truncate"><i class="fas fa-map-marker-alt text-brand"></i> <?= htmlspecialchars($ad['location']) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-span-1 sm:col-span-2 md:col-span-3 lg:col-span-4 bg-white border border-slate-200 border-dashed rounded-2xl p-16 text-center text-slate-500 flex flex-col items-center justify-center">
                <i class="far fa-heart text-5xl mb-4 text-slate-300"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No favorites yet</h3>
                <p class="mb-6">Click the heart icon on any ad to save it here!</p>
                <a href="index.php" class="bg-brand text-white font-bold px-8 py-2.5 rounded-full hover:bg-brand-light transition">
                    Explore Ads
                </a>
            </div>
        <?php endif; ?>
    </div>


<?php include 'includes/footer.php'; ?>
