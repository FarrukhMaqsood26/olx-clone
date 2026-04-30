<?php
require_once 'includes/config.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// 1. Fetch Recently Viewed Ads (from cookie)
$recent_ads = [];
if (isset($_COOKIE['recently_viewed'])) {
    $recent_ids = json_decode($_COOKIE['recently_viewed'], true);
    if (!empty($recent_ids)) {
        $placeholders = implode(',', array_fill(0, count($recent_ids), '?'));
        $recent_stmt = $pdo->prepare("
            SELECT a.id, a.title, a.price, a.location, a.created_at, 
                   (SELECT image_path FROM ad_images WHERE ad_id = a.id AND is_primary = 1 LIMIT 1) as main_image,
                   (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND ad_id = a.id) as is_favorited
            FROM ads a
            WHERE a.id IN ($placeholders) AND a.status = 'active'
            ORDER BY FIELD(a.id, " . implode(',', $recent_ids) . ")
        ");
        $recent_stmt->execute(array_merge([$user_id], $recent_ids));
        $recent_ads = $recent_stmt->fetchAll();
    }
}

// 2. Fetch Categories with their IDs
$cat_stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC");
$categories = $cat_stmt->fetchAll();

function getAdsByCategory($pdo, $categoryId, $userId, $limit = 8)
{
    $limit = (int) $limit;
    $categoryId = (int) $categoryId;
    $userId = (int) $userId;
    $stmt = $pdo->prepare("
        SELECT v.*, v.primary_image as main_image,
               (SELECT COUNT(*) FROM favorites WHERE user_id = :uid AND ad_id = v.id) as is_favorited
        FROM view_active_ads v
        WHERE v.category_id = :cat
        ORDER BY v.created_at DESC
        LIMIT $limit
    ");
    $stmt->execute([':uid' => $userId, ':cat' => $categoryId]);
    return $stmt->fetchAll();
}

include 'includes/header.php';
?>

<!-- Category Strip -->
<div class="flex flex-wrap justify-center gap-2 sm:gap-3 pb-4 mb-6 sm:mb-8 px-2">
    <a href="search.php"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-th-large text-brand mr-1 sm:mr-2"></i> ALL</a>
    <a href="search.php?category=mobiles"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-mobile-alt text-accent mr-1 sm:mr-2"></i> Mobiles</a>
    <a href="search.php?category=vehicles"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-car text-accent mr-1 sm:mr-2"></i> Vehicles</a>
    <a href="search.php?category=property"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-home text-accent mr-1 sm:mr-2"></i> Property</a>
    <a href="search.php?category=electronics"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-tv text-accent mr-1 sm:mr-2"></i> Electronics</a>
    <a href="search.php?category=bikes"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-motorcycle text-accent mr-1 sm:mr-2"></i> Bikes</a>
    <a href="search.php?category=jobs"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-briefcase text-accent mr-1 sm:mr-2"></i> Jobs</a>
    <a href="search.php?category=furniture"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-couch text-accent mr-1 sm:mr-2"></i> Furniture</a>
    <a href="search.php?category=animals"
        class="snap-start flex-none px-4 sm:px-5 py-2 sm:py-2.5 rounded-full bg-white border border-slate-200 text-slate-700 font-medium text-xs sm:text-sm shadow-sm hover:border-brand hover:text-brand transition whitespace-nowrap"><i
            class="fas fa-paw text-accent mr-1 sm:mr-2"></i> Animals</a>
</div>

<!-- Hero Banner -->
<section
    class="bg-[#3a77ff] rounded-2xl sm:rounded-3xl p-6 sm:p-10 md:p-12 text-center mb-8 sm:mb-12 shadow-[0_20px_50px_rgba(58,119,255,0.2)] relative overflow-hidden group">
    <!-- Creative Background Elements -->
    <div
        class="absolute -top-24 -left-24 w-64 h-64 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700">
    </div>
    <div
        class="absolute -bottom-24 -right-24 w-96 h-96 bg-brand-light/20 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700 delay-100">
    </div>
    <!-- Dot pattern inside hero -->
    <div class="absolute inset-0 opacity-10"
        style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 20px 20px;">
    </div>

    <div class="relative z-10 mx-auto">
        <span
            class="inline-block px-3 sm:px-4 py-1 sm:py-1.5 bg-white/20 backdrop-blur-md text-white text-[10px] sm:text-xs font-bold uppercase tracking-widest rounded-full mb-4 sm:mb-6 border border-white/30 animate-pulse">
            New Marketplace Experience
        </span>
        <h1
            class="text-3xl sm:text-4xl md:text-6xl font-extrabold text-white mb-4 sm:mb-6 tracking-tight leading-tight">
            Buy, Sell &amp; <span class="text-white/80">Connect</span> Instantly
        </h1>
        <p class="text-sm sm:text-lg md:text-xl text-blue-50/90 mb-8 sm:mb-10 leading-relaxed px-2 sm:px-0">
            The most secure way to trade in your community. Find everything from mobiles to property in minutes.
        </p>
        <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center items-center">
            <a href="post-ad.php"
                class="w-full sm:w-auto bg-white text-[#3a77ff] font-bold px-6 py-3 sm:px-10 sm:py-4 rounded-xl sm:rounded-2xl hover:bg-blue-50 transition-all shadow-xl hover:-translate-y-1 flex items-center justify-center gap-2 group/btn">
                <i class="fas fa-plus-circle text-lg sm:text-xl transition-transform group-hover/btn:rotate-90"></i>
                Start Selling
            </a>
            <a href="search.php"
                class="w-full sm:w-auto bg-transparent border-2 border-white/40 text-white font-bold px-6 py-2.5 sm:px-10 sm:py-3.5 rounded-xl sm:rounded-2xl hover:bg-white/10 transition-all flex items-center justify-center gap-2">
                <i class="fas fa-search"></i> Browse All
            </a>
        </div>
    </div>
</section>

<!-- ===== RECENTLY VIEWED SLIDE ROW ===== -->
<?php if (!empty($recent_ads)): ?>
    <section class="mb-12">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="far fa-clock text-blue-500 text-sm"></i>
                </span>
                Recently Viewed
            </h2>
            <button
                onclick="document.cookie='recently_viewed=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;'; location.reload();"
                class="text-xs font-bold text-slate-400 hover:text-red-500 transition flex items-center gap-1">
                <i class="fas fa-trash-alt text-[10px]"></i> Clear
            </button>
        </div>
        <div class="slide-row-wrapper px-1">
            <div class="slide-row" id="recent-row">
                <?php foreach ($recent_ads as $ad): ?>
                    <?php include 'includes/ad_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- ===== CATEGORY SLIDE ROWS ===== -->
<?php
$catIcons = [
    'mobiles' => ['icon' => 'fa-mobile-alt', 'color' => 'bg-purple-100 text-purple-500'],
    'vehicles' => ['icon' => 'fa-car', 'color' => 'bg-blue-100 text-blue-500'],
    'property' => ['icon' => 'fa-home', 'color' => 'bg-green-100 text-green-500'],
    'electronics' => ['icon' => 'fa-tv', 'color' => 'bg-yellow-100 text-yellow-600'],
    'bikes' => ['icon' => 'fa-motorcycle', 'color' => 'bg-red-100 text-red-500'],
    'jobs' => ['icon' => 'fa-briefcase', 'color' => 'bg-indigo-100 text-indigo-500'],
    'furniture' => ['icon' => 'fa-couch', 'color' => 'bg-amber-100 text-amber-600'],
    'animals' => ['icon' => 'fa-paw', 'color' => 'bg-pink-100 text-pink-500'],
];
$rowIndex = 0;
foreach ($categories as $cat):
    $catAds = getAdsByCategory($pdo, $cat['id'], $user_id);
    if (empty($catAds))
        continue;
    $rowId = 'cat-row-' . $rowIndex++;
    $slug = $cat['slug'] ?? '';
    $iconInfo = $catIcons[$slug] ?? ['icon' => 'fa-tag', 'color' => 'bg-slate-100 text-slate-500'];
    ?>
    <section class="mb-10">
        <!-- Section header -->
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg <?= $iconInfo['color'] ?> flex items-center justify-center">
                    <i class="fas <?= $iconInfo['icon'] ?> text-sm"></i>
                </span>
                <?= htmlspecialchars($cat['name']) ?>
            </h2>
            <a href="search.php?category=<?= htmlspecialchars($slug) ?>"
                class="text-sm font-bold text-blue-600 hover:text-blue-800 transition flex items-center gap-1 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-full">
                See All <i class="fas fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <!-- Slide row -->
        <div class="slide-row-wrapper px-1">
            <div class="slide-row" id="<?= $rowId ?>">
                <?php foreach ($catAds as $ad): ?>
                    <?php include 'includes/ad_card.php'; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Divider between sections -->
        <div class="mt-8 border-t border-slate-200/70"></div>
    </section>
<?php endforeach; ?>

<script>
    /**
     * Slide a row left (-1) or right (+1) by one card width
     */
    function slideRow(rowId, direction) {
        const row = document.getElementById(rowId);
        if (!row) return;
        // Scroll by ~card width: first child width + gap
        const card = row.firstElementChild;
        const cardW = card ? card.offsetWidth + 16 : 260; // 16 = gap 1rem
        row.scrollBy({ left: direction * cardW * 2, behavior: 'smooth' });
    }
</script>

<?php include 'includes/footer.php'; ?>