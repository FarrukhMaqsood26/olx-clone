<?php
require_once 'includes/config.php';

// 1. Fetch Recommended Ads (Latest 4)
$rec_stmt = $pdo->prepare("
    SELECT a.id, a.title, a.price, a.location, a.created_at, 
           (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image,
           (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND ad_id = a.id) as is_favorited
    FROM ads a
    WHERE a.status = 'active'
    ORDER BY a.created_at DESC
    LIMIT 4
");
$rec_stmt->execute([isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0]);
$recommendedAds = $rec_stmt->fetchAll();

// 2. Fetch Categories and their top ads
$cat_stmt = $pdo->query("SELECT id, name, slug, icon FROM categories ORDER BY name ASC");
$allCategories = $cat_stmt->fetchAll();

$categoryGalleries = [];
foreach ($allCategories as $cat) {
    $ad_stmt = $pdo->prepare("
        SELECT a.id, a.title, a.price, a.location, a.created_at, 
               (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image,
               (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND ad_id = a.id) as is_favorited
        FROM ads a
        WHERE a.status = 'active' AND a.category_id = ?
        ORDER BY a.created_at DESC
        LIMIT 4
    ");
    $ad_stmt->execute([isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0, $cat['id']]);
    $ads = $ad_stmt->fetchAll();
    
    if (count($ads) > 0) {
        $categoryGalleries[] = [
            'info' => $cat,
            'ads' => $ads
        ];
    }
}

include 'includes/header.php'; 
?>

<style>
    @keyframes floating {
        0% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-20px) rotate(2deg); }
        100% { transform: translateY(0px) rotate(0deg); }
    }
    .hero-creative {
        background: radial-gradient(circle at top left, #1e1b4b 0%, #050505 100%);
        position: relative;
        overflow: hidden;
    }
    .neon-glow {
        position: absolute;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255, 45, 85, 0.2) 0%, transparent 70%);
        filter: blur(60px);
        z-index: 0;
    }
    .glass-bento {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .outline-heading {
        -webkit-text-stroke: 1.5px rgba(255, 255, 255, 0.3);
        color: transparent;
    }
    .noise-bg {
        background-image: url('https://grainy-gradients.vercel.app/noise.svg');
        opacity: 0.1;
        pointer-events: none;
    }
</style>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full space-y-24">
    
    <!-- Redone Creative Hero -->
    <section class="hero-creative min-h-[650px] rounded-[3.5rem] p-8 md:p-20 relative flex items-center mt-4 group">
        <div class="noise-bg absolute inset-0"></div>
        <div class="neon-glow top-0 left-0 animate-pulse"></div>
        <div class="neon-glow bottom-0 right-0 animate-pulse" style="background: radial-gradient(circle, rgba(255, 204, 0, 0.15) 0%, transparent 70%);"></div>

        <div class="relative z-10 w-full grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
            <!-- Left: Impact Content -->
            <div class="lg:col-span-7 space-y-10">
                <div class="inline-flex items-center gap-3 px-5 py-2 rounded-full border border-white/10 bg-white/5 text-white text-[11px] font-black uppercase tracking-[0.4em] backdrop-blur-md">
                    <span class="w-1.5 h-1.5 bg-accent rounded-full animate-ping"></span>
                    Discover the Future
                </div>
                
                <div class="space-y-2">
                    <h1 class="text-6xl md:text-9xl font-black text-white leading-[0.9] tracking-tighter">
                        ULTIMATE <br>
                        <span class="outline-heading">FREEDOM</span>
                    </h1>
                    <p class="text-lg md:text-xl text-slate-400 font-medium max-w-lg leading-relaxed pt-4">
                        Experience Pakistan's most creative marketplace. Join the revolution of <span class="text-white border-b-2 border-accent">smart trading</span>.
                    </p>
                </div>

                <!-- Modern Creative Search -->
                <div class="relative max-w-2xl group/search">
                    <div class="absolute -inset-1 bg-gradient-to-r from-accent via-accent-vibrant to-accent rounded-[2rem] blur opacity-25 group-hover/search:opacity-50 transition duration-1000"></div>
                    <form action="search.php" method="GET" class="relative flex p-1.5 bg-white rounded-[1.8rem] shadow-2xl transition hover:scale-[1.01]">
                        <div class="flex-1 relative flex items-center">
                            <i class="fas fa-search absolute left-6 text-slate-400"></i>
                            <input type="text" name="q" placeholder="What's on your mind today?" 
                                class="w-full pl-16 pr-6 py-4 rounded-2xl outline-none text-slate-900 font-bold placeholder-slate-400 text-lg">
                        </div>
                        <button class="bg-brand text-white px-10 py-4 rounded-[1.4rem] font-black hover:bg-black transition-all flex items-center gap-3 tracking-widest text-sm">
                            EXPLORE <i class="fas fa-chevron-right text-accent"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Bento Feature Grid -->
            <div class="lg:col-span-5 grid grid-cols-2 gap-6 relative">
                <div class="glass-bento p-8 rounded-[2.5rem] space-y-6 hover:translate-y-[-10px] transition-transform duration-500">
                    <div class="w-14 h-14 rounded-2xl bg-accent flex items-center justify-center text-white text-2xl shadow-[0_0_30px_rgba(255,45,85,0.4)]">
                        <i class="fas fa-bolt-lightning"></i>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">4ms</div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Live Engine</div>
                    </div>
                </div>
                <div class="glass-bento p-8 rounded-[2.5rem] space-y-6 mt-12 hover:translate-y-[-10px] transition-transform duration-500 delay-75">
                    <div class="w-14 h-14 rounded-2xl bg-accent-vibrant flex items-center justify-center text-slate-900 text-2xl shadow-[0_0_30px_rgba(255,204,0,0.4)]">
                        <i class="fas fa-shield-circle-check"></i>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">99%</div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Trust Core</div>
                    </div>
                </div>
                <div class="glass-bento p-8 rounded-[2.5rem] space-y-6 hover:translate-y-[-10px] transition-transform duration-500 delay-150">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-500 flex items-center justify-center text-white text-2xl">
                        <i class="fas fa-location-arrow"></i>
                    </div>
                    <div>
                        <div class="text-4xl font-black text-white">local</div>
                        <div class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mt-1">Hyper Layer</div>
                    </div>
                </div>
                <a href="post-ad.php" class="bg-accent p-8 rounded-[2.5rem] space-y-6 mt-12 hover:scale-[1.05] transition-transform duration-500 shadow-2xl relative overflow-hidden group/ad">
                    <div class="absolute inset-0 bg-gradient-to-br from-white/10 to-transparent"></div>
                    <i class="fas fa-plus text-white text-5xl relative z-10 group-hover/ad:rotate-90 transition-transform duration-500"></i>
                    <div class="text-2xl font-black text-white relative z-10 leading-tight">POST AD <br>NOW</div>
                </a>
            </div>
        </div>
    </section>

    <!-- Categories: Glass Cards -->
    <div class="flex gap-8 overflow-x-auto pb-4 snap-x no-scrollbar px-2">
        <?php foreach($allCategories as $cat): ?>
        <a href="search.php?category=<?= $cat['slug'] ?>" class="snap-start flex-none flex flex-col items-center gap-5 group">
            <div class="w-24 h-24 rounded-[2.5rem] bg-white border border-slate-200 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.05)] flex items-center justify-center text-3xl text-slate-600 group-hover:border-accent group-hover:text-accent group-hover:shadow-[8px_8px_0px_0px_rgba(255,45,85,1)] group-hover:translate-x-[-4px] group-hover:translate-y-[-4px] transition-all duration-300">
                <i class="fas <?= $cat['icon'] ?>"></i>
            </div>
            <span class="text-[10px] font-black text-slate-500 group-hover:text-white transition uppercase tracking-[0.3em]"><?= $cat['name'] ?></span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- 1. Recommended: High Contrast Grid -->
    <section>
        <div class="flex justify-between items-end mb-16 px-2">
            <div>
                <h2 class="text-7xl font-black text-white tracking-tighter italic">HOT DROPS</h2>
                <div class="h-2 w-32 bg-accent mt-4"></div>
            </div>
            <a href="search.php" class="flex items-center gap-4 text-accent font-black tracking-[0.2em] text-xs hover:gap-6 transition-all">VIEW ALL GALLERY <i class="fas fa-arrow-right"></i></a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-10">
            <?php foreach($recommendedAds as $ad): ?>
                <?php $isFav = $ad['is_favorited'] > 0; ?>
                <div class="group bg-white border-4 border-black rounded-[3rem] overflow-hidden hover:shadow-[15px_15px_0px_0px_rgba(255,45,85,1)] hover:translate-x-[-6px] hover:translate-y-[-6px] transition-all duration-500 flex flex-col relative">
                    <button onclick="toggleFavorite(<?= $ad['id'] ?>, this)" class="absolute top-6 right-6 bg-white border-2 border-black w-12 h-12 rounded-full flex items-center justify-center z-20 transition hover:bg-slate-50">
                        <i class="<?= $isFav ? 'fas text-accent' : 'far text-slate-300' ?> fa-heart text-xl"></i>
                    </button>
                    
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="block flex-1">
                        <div class="aspect-square overflow-hidden bg-slate-100 relative group-hover:bg-slate-200 transition-colors duration-500">
                            <?php $img = $ad['main_image'] ?: 'assets/images/placeholder.png'; ?>
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-1000 grayscale-[0.3] group-hover:grayscale-0">
                            <div class="absolute bottom-6 left-6 bg-brand text-white text-[10px] font-black px-4 py-2 uppercase tracking-[0.2em] rounded-xl shadow-xl">AUTHENTIC</div>
                            <button onclick="event.preventDefault(); openLightbox('<?= htmlspecialchars($img) ?>')" class="absolute inset-0 bg-accent/20 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                                <span class="bg-white text-brand font-black px-6 py-2.5 rounded-full text-[10px] tracking-widest shadow-2xl">PREVIEW ITEM</span>
                            </button>
                        </div>
                        <div class="p-8 flex flex-col items-center text-center">
                            <div class="text-3xl font-black text-brand mb-2 tracking-tighter">Rs <?= number_format($ad['price']) ?></div>
                            <h3 class="text-slate-500 font-bold text-sm tracking-tight capitalize mb-6"><?= htmlspecialchars($ad['title']) ?></h3>
                            <div class="flex items-center gap-4 text-[9px] font-black text-slate-400 uppercase tracking-widest pt-4 border-t border-slate-100 w-full justify-center">
                                <span class="flex items-center gap-2"><i class="fas fa-location-pin text-accent"></i> <?= htmlspecialchars($ad['location']) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- 2. Dynamic Categories -->
    <?php foreach ($categoryGalleries as $gallery): ?>
    <section class="pt-16">
        <div class="flex justify-between items-end mb-16 border-b border-white/10 pb-10">
            <div>
                <h2 class="text-5xl font-black text-white tracking-tighter uppercase italic py-2">
                    <span class="text-accent">#</span><?= $gallery['info']['name'] ?>
                </h2>
                <div class="flex gap-2 mt-2">
                    <div class="h-1 w-8 bg-accent"></div>
                    <div class="h-1 w-4 bg-accent-vibrant"></div>
                </div>
            </div>
            <a href="search.php?category=<?= $gallery['info']['slug'] ?>" class="text-white/30 font-black text-[10px] uppercase tracking-[0.3em] hover:text-accent transition">ENTER CHANNEL <i class="fas fa-chevron-right ml-2 text-xs"></i></a>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-12">
            <?php foreach($gallery['ads'] as $ad): ?>
                <?php $isFav = $ad['is_favorited'] > 0; ?>
                <div class="group bg-slate-950 border-2 border-white/5 rounded-[3rem] overflow-hidden hover:border-accent transition-all duration-700 flex flex-col relative shadow-2xl">
                    <button onclick="toggleFavorite(<?= $ad['id'] ?>, this)" class="absolute top-6 right-6 bg-black/60 backdrop-blur-xl text-white w-11 h-11 rounded-full flex items-center justify-center z-20 transition hover:bg-accent border border-white/10">
                        <i class="<?= $isFav ? 'fas text-white' : 'far text-white/50' ?> fa-heart text-lg"></i>
                    </button>
                    
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="block flex-1">
                        <div class="aspect-[4/5] overflow-hidden bg-slate-900 relative">
                            <?php $img = $ad['main_image'] ?: 'assets/images/placeholder.png'; ?>
                            <img src="<?= htmlspecialchars($img) ?>" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 group-hover:scale-105 transition duration-1000">
                        </div>
                        <div class="p-8">
                            <div class="text-3xl font-black text-white mb-2 tracking-tighter">Rs <?= number_format($ad['price']) ?></div>
                            <h3 class="text-slate-500 font-bold line-clamp-1 mb-6 text-[10px] uppercase tracking-[0.2em]"><?= htmlspecialchars($ad['title']) ?></h3>
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-px bg-white/10"></div>
                                <span class="text-[9px] font-black text-slate-600 uppercase tracking-widest"><?= htmlspecialchars($ad['location']) ?></span>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>

</main>

<div class="bg-black py-32 border-t border-white/5 mt-32 relative overflow-hidden">
    <div class="neon-glow top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 opacity-30"></div>
    <div class="max-w-7xl mx-auto px-4 text-center relative z-10">
        <h3 class="text-7xl md:text-9xl font-black text-white tracking-tighter italic mb-8">TRADE NOW</h3>
        <p class="text-slate-500 mb-12 max-w-2xl mx-auto font-black text-sm uppercase tracking-[0.4em]">Zero fees. Infinite reach. Join the drop.</p>
        <a href="post-ad.php" class="inline-block bg-accent text-white font-black px-16 py-7 rounded-full hover:scale-110 transition-all shadow-[0_0_80px_rgba(255,45,85,0.4)] tracking-[0.2em] text-sm">
            POST YOUR ADS <i class="fas fa-plus ml-4"></i>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
