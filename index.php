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
    <div class="flex gap-3 overflow-x-auto pb-4 mb-8 snap-x no-scrollbar" style="scrollbar-width: none;">
        <a href="search.php" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-th-large text-brand mr-2"></i> ALL</a>
        <a href="search.php?category=mobiles" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-mobile-alt text-accent mr-2"></i> Mobiles</a>
        <a href="search.php?category=vehicles" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-car text-accent mr-2"></i> Vehicles</a>
        <a href="search.php?category=property" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-home text-accent mr-2"></i> Property</a>
        <a href="search.php?category=electronics" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-tv text-accent mr-2"></i> Electronics</a>
        <a href="search.php?category=bikes" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-motorcycle text-accent mr-2"></i> Bikes</a>
        <a href="search.php?category=jobs" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-briefcase text-accent mr-2"></i> Jobs</a>
        <a href="search.php?category=furniture" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-couch text-accent mr-2"></i> Furniture</a>
        <a href="search.php?category=animals" class="snap-start flex-none px-5 py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i class="fas fa-paw text-accent mr-2"></i> Animals</a>
    </div>

    <!-- Hero Banner -->
    <section class="bg-white border border-slate-200 rounded-2xl p-8 md:p-16 text-center mb-10 shadow-sm relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-brand/5 to-accent/5"></div>
        <div class="relative z-10">
            <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">Discover Amazing Deals</h1>
            <p class="text-base md:text-lg text-slate-600 mb-8 max-w-2xl mx-auto">Buy & sell anything near you with safety and ease. Join the largest local marketplace.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="post-ad.php" class="w-full sm:w-auto bg-brand text-white font-bold px-8 py-3.5 rounded-full hover:bg-brand-light transition shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                    <i class="fas fa-plus text-accent"></i> Start Selling
                </a>
                <a href="search.php" class="w-full sm:w-auto bg-white text-brand border-2 border-slate-200 font-bold px-8 py-3 rounded-full hover:border-brand transition flex items-center justify-center gap-2">
                    <i class="fas fa-compass"></i> Explore All
                </a>
            </div>
        </div>
    </section>

    <!-- Product Grid: Fresh Recommendations -->
    <section>
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-slate-900">Fresh recommendations</h2>
            <a href="search.php" class="text-brand font-bold text-sm hover:underline flex items-center gap-1">
                View All <i class="fas fa-arrow-right"></i>
            </a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php if (count($ads) > 0): ?>
                <?php foreach($ads as $ad): ?>
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="group bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col relative block">
                        <button class="absolute top-3 right-3 bg-white/90 backdrop-blur text-slate-400 w-9 h-9 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 z-10 transition">
                            <i class="far fa-heart text-lg"></i>
                        </button>
                        <?php 
                            $img = $ad['main_image'] ? $ad['main_image'] : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80'; 
                        ?>
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
                    <i class="fas fa-box-open text-5xl mb-4 text-slate-300"></i>
                    <h3 class="text-xl font-bold text-slate-700 mb-2">No ads found</h3>
                    <p class="mb-6">Be the first one to post a deal in your area!</p>
                    <a href="post-ad.php" class="bg-brand text-white font-bold px-6 py-2.5 rounded-full hover:bg-brand-light transition flex items-center gap-2">
                        <i class="fas fa-plus"></i> Post an Ad
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>
