<?php
require_once 'includes/config.php';

// Recently Viewed Tracking
$ad_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($ad_id > 0) {
    if (!isset($_COOKIE['recently_viewed'])) {
        $recent = [];
    } else {
        $recent = json_decode($_COOKIE['recently_viewed'], true);
        if (!is_array($recent)) $recent = [];
    }
    
    // Remove if already exists and add to front
    $recent = array_diff($recent, [$ad_id]);
    array_unshift($recent, $ad_id);
    
    // Keep last 10
    $recent = array_slice($recent, 0, 10);
    
    setcookie('recently_viewed', json_encode($recent), time() + (86400 * 30), "/"); // 30 days
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: index.php");
    exit;
}

// Fetch ad details
$stmt = $pdo->prepare("
    SELECT a.*, u.name as seller_name, u.avatar, u.phone, u.created_at as member_since, c.name as category_name,
           (SELECT COUNT(*) FROM favorites WHERE user_id = ? AND ad_id = a.id) as is_favorited
    FROM ads a
    JOIN users u ON a.user_id = u.id
    LEFT JOIN categories c ON a.category_id = c.id
    WHERE a.id = ?
");
$stmt->execute([isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0, $id]);
$ad = $stmt->fetch();

if (!$ad) {
    include 'includes/header.php';
    echo '<div class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white border border-slate-200 border-dashed rounded-2xl max-w-lg w-full p-12 text-center shadow-sm">
            <i class="fas fa-search text-5xl text-slate-300 mb-6 tracking-tight"></i>
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Ad Not Found</h2>
            <p class="text-slate-500 mb-8">This ad may have been removed or is no longer available.</p>
            <a href="index.php" class="inline-flex items-center gap-2 bg-brand hover:bg-brand-light text-white font-bold py-3 px-8 rounded-full transition shadow-sm">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>';
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


    <div class="grid grid-cols-1 lg:grid-cols-[1.6fr_1fr] gap-8 items-start">
        
        <!-- Left: Images and Details -->
        <div class="space-y-8 min-w-0">
            <div class="bg-black border border-slate-200 rounded-2xl overflow-hidden shadow-sm relative group">
                <?php if($ad['status'] === 'sold'): ?>
                    <div class="absolute top-4 left-4 bg-purple-600 text-white px-4 py-1.5 rounded-full font-bold text-sm tracking-wider z-20 shadow-md">SOLD</div>
                <?php endif; ?>
                
                <div class="relative aspect-[4/3] sm:aspect-[16/9] w-full bg-slate-900 flex items-center justify-center overflow-hidden">
                    <?php if (count($images) > 0): ?>
                        <!-- Always-visible prev/next arrows -->
                        <?php if (count($images) > 1): ?>
                        <button onclick="navigateImage(-1)" class="ad-img-nav-btn prev" id="adPrevBtn" title="Previous image">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <button onclick="navigateImage(1)" class="ad-img-nav-btn next" id="adNextBtn" title="Next image">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                        <!-- Image counter pill -->
                        <div class="img-counter" id="imgCounter">1 / <?= count($images) ?></div>
                        <?php endif; ?>
                        <!-- Zoom / fullscreen icon -->
                        <div class="zoom-icon-overlay" onclick="openFullscreen()" title="View fullscreen">
                            <i class="fas fa-expand text-sm"></i>
                        </div>
                        <img src="<?= htmlspecialchars(get_ad_image($images[0]['image_path'])) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" id="mainAdImage" 
                             class="w-full h-full object-contain cursor-zoom-in transition-opacity duration-300"
                             onclick="openFullscreen()">
                    <?php else: ?>
                        <div class="text-center text-slate-500">
                            <i class="fas fa-image text-6xl mb-4 opacity-50"></i>
                            <p class="font-medium">No Image Available</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if(count($images) > 1): ?>
            <div class="flex gap-3 overflow-x-auto pb-2 no-scrollbar">
                <?php foreach($images as $i => $img): ?>
                    <button class="flex-none snap-start focus:outline-none" onclick="changeMainImage(this, '<?= htmlspecialchars(get_ad_image($img['image_path'])) ?>')">
                        <img src="<?= htmlspecialchars(get_ad_image($img['image_path'])) ?>" alt="Thumbnail <?= $i+1 ?>" class="ad-thumb cursor-pointer w-20 h-16 sm:w-24 sm:h-20 object-cover rounded-lg border-2 <?= $i === 0 ? 'border-brand opacity-100 shadow-sm' : 'border-transparent opacity-60 hover:opacity-100 hover:border-slate-300' ?> transition">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                    <i class="fas fa-align-left text-brand"></i> Description
                </h3>
                <div class="prose prose-slate max-w-none text-slate-600 whitespace-pre-wrap leading-relaxed text-[15px]"><?= htmlspecialchars($ad['description']) ?></div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 sm:p-8">
                <h3 class="text-xl font-bold text-slate-900 flex items-center gap-2 mb-4 border-b border-slate-100 pb-4">
                    <i class="fas fa-info-circle text-brand"></i> Details
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Condition</span>
                        <span class="font-bold text-slate-800 capitalize"><?= $ad['condition_type'] ?></span>
                    </div>
                    <?php if($ad['category_name']): ?>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col gap-1">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Category</span>
                        <span class="font-bold text-slate-800 truncate"><?= htmlspecialchars($ad['category_name']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col gap-1 col-span-2 md:col-span-1">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Location</span>
                        <span class="font-bold text-slate-800 truncate"><?= htmlspecialchars($ad['location']) ?></span>
                    </div>
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 flex flex-col gap-1 col-span-2 md:col-span-1">
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Posted</span>
                        <span class="font-bold text-slate-800"><?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Price and Seller Info -->
        <div class="lg:sticky lg:top-24 space-y-4 sm:space-y-6">
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-4 sm:p-6 md:p-8">
                <div class="text-3xl sm:text-4xl font-extrabold text-brand mb-2 sm:mb-4">Rs <?= number_format($ad['price']) ?></div>
                <h1 class="text-xl sm:text-2xl font-bold text-slate-900 leading-tight mb-3 sm:mb-4"><?= htmlspecialchars($ad['title']) ?></h1>
                
                <div class="flex flex-wrap gap-4 text-sm font-medium text-slate-500 mb-6">
                    <span class="flex items-center gap-1.5"><i class="fas fa-map-marker-alt text-slate-400"></i> <?= htmlspecialchars($ad['location']) ?></span>
                    <span class="flex items-center gap-1.5"><i class="far fa-clock text-slate-400"></i> <?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                </div>
                
                <div class="h-px w-full bg-slate-100 mb-6"></div>
                
                <div class="flex items-center gap-4 bg-slate-50 border border-slate-100 rounded-xl p-4 mb-6">
                    <div class="w-14 h-14 rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0">
                        <i class="fas fa-user text-2xl"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-900 text-lg"><?= htmlspecialchars(explode(' ', trim($ad['seller_name']))[0]) ?></div>
                        <div class="text-xs font-semibold text-slate-500">Member since <?= date('M Y', strtotime($ad['member_since'])) ?></div>
                    </div>
                </div>

                <div class="space-y-3">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_id'] != $ad['user_id']): ?>
                            <a href="chat.php?partner_id=<?= $ad['user_id'] ?>" class="flex items-center justify-center gap-2 w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-xl shadow-sm transition">
                                <i class="far fa-envelope"></i> Message Seller
                            </a>
                        <?php else: ?>
                            <div class="text-center font-semibold text-brand bg-brand/5 rounded-xl p-3 mb-3 border border-brand/10">
                                <i class="fas fa-info-circle mr-1"></i> This is your ad
                            </div>
                            <a href="profile.php" class="flex items-center justify-center gap-2 w-full bg-white border border-slate-300 hover:border-brand text-slate-700 hover:text-brand font-bold py-3 px-4 rounded-xl transition shadow-sm">
                                <i class="fas fa-cog"></i> Manage in Profile
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="flex items-center justify-center gap-2 w-full bg-white border-2 border-slate-200 hover:border-brand hover:text-brand text-slate-700 font-bold py-3.5 px-4 rounded-xl transition shadow-sm">
                            <i class="far fa-envelope"></i> Login to Message
                        </a>
                    <?php endif; ?>
                    
                    <?php if($ad['phone']): ?>
                    <div class="flex items-center justify-between bg-white border border-slate-200 rounded-xl p-3.5">
                        <div class="flex items-center gap-3 font-bold text-slate-800">
                            <i class="fas fa-phone text-emerald-500 bg-emerald-50 w-8 h-8 rounded-full flex items-center justify-center text-sm"></i> 
                            <?= htmlspecialchars($ad['phone']) ?>
                        </div>
                        <button onclick="navigator.clipboard.writeText('<?= htmlspecialchars($ad['phone']) ?>'); showToast('Number copied!', 'success');" class="text-sm font-bold text-accent hover:text-brand transition px-2">
                            Copy
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php $isFav = (isset($ad['is_favorited']) && $ad['is_favorited'] > 0); ?>
                    <button onclick="toggleFavorite(<?= $ad['id'] ?>, this)" class="flex items-center justify-center gap-2 w-full bg-white border border-slate-200 hover:border-red-500 hover:bg-red-50 text-slate-600 font-bold py-3.5 px-4 rounded-xl transition shadow-sm group">
                        <i class="<?= $isFav ? 'fas text-red-500' : 'far text-slate-400 group-hover:text-red-500' ?> fa-heart transition"></i> 
                        <span class="<?= $isFav ? 'text-red-500' : '' ?>"><?= $isFav ? 'Favorited' : 'Add to Favorites' ?></span>
                    </button>
                </div>
            </div>
            
            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 text-center text-sm text-slate-500">
                <i class="fas fa-shield-alt text-brand text-2xl mb-3 block"></i>
                <p>Stay safe. Meet in a public place and inspect the item before paying.</p>
            </div>
        </div>

    </div><!-- End of main grid -->
    
    <!-- Similar Ads -->
    <?php if(count($similarAds) > 0): ?>
    <div class="pt-8 border-t border-slate-200 mt-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6">Similar Ads</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach($similarAds as $sim): ?>
                <a href="ad.php?id=<?= $sim['id'] ?>" class="group bg-white border border-slate-200 rounded-xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col relative block">
                    <button class="absolute top-3 right-3 bg-white/90 backdrop-blur text-slate-400 w-9 h-9 rounded-full flex items-center justify-center shadow-sm hover:text-red-500 z-10 transition">
                        <i class="far fa-heart text-lg"></i>
                    </button>
                    <?php $img = get_ad_image($sim['main_image']); ?>
                    <div class="aspect-[4/3] overflow-hidden border-b border-slate-100">
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($sim['title']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" loading="lazy">
                    </div>
                    <div class="p-4 flex flex-col flex-1">
                        <div class="text-xl font-bold text-slate-900 mb-1">Rs <?= number_format($sim['price']) ?></div>
                        <div class="text-sm text-slate-600 line-clamp-2 mb-4 flex-1 leading-snug"><?= htmlspecialchars($sim['title']) ?></div>
                        <div class="text-[11px] font-medium text-slate-400 uppercase tracking-wide flex justify-between items-center mt-auto">
                            <span class="flex items-center gap-1 truncate max-w-[80%]"><i class="fas fa-map-marker-alt text-brand"></i> <?= htmlspecialchars($sim['location']) ?></span>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>


<!-- Fullscreen Modal -->
<div id="fullscreenModal" class="fixed inset-0 z-[60] bg-black/96 hidden flex-col items-center justify-center" onclick="handleFsBackdropClick(event)">
    <!-- Close button -->
    <button onclick="closeFullscreen()" class="absolute top-5 right-5 text-white/80 hover:text-white transition z-[70] w-12 h-12 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-lg" title="Close (Esc)">
        <i class="fas fa-times"></i>
    </button>
    <!-- Counter -->
    <div class="absolute top-5 left-1/2 -translate-x-1/2 text-white/70 text-sm font-bold tracking-widest z-[70]" id="fsCounter"></div>

    <!-- Prev arrow -->
    <button onclick="navigateImage(-1)" class="fs-nav-btn prev" id="fsPrevBtn" title="Previous">
        <i class="fas fa-chevron-left"></i>
    </button>

    <div class="relative flex items-center justify-center w-full h-full px-20 py-16">
        <img id="fullscreenImage" src="" alt="Fullscreen" 
             class="max-w-full max-h-full object-contain rounded-xl shadow-2xl select-none transition-opacity duration-200">
    </div>

    <!-- Next arrow -->
    <button onclick="navigateImage(1)" class="fs-nav-btn next" id="fsNextBtn" title="Next">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<script>
let currentImageIndex = 0;
const adImages = <?= json_encode(array_map('get_ad_image', array_column($images, 'image_path'))) ?>;

function updateImageUI() {
    const mainImg   = document.getElementById('mainAdImage');
    const fsImg     = document.getElementById('fullscreenImage');
    const counter   = document.getElementById('imgCounter');
    const fsCounter = document.getElementById('fsCounter');

    if (mainImg) mainImg.src = adImages[currentImageIndex];
    if (fsImg)   fsImg.src   = adImages[currentImageIndex];
    if (counter)   counter.textContent   = (currentImageIndex + 1) + ' / ' + adImages.length;
    if (fsCounter) fsCounter.textContent = (currentImageIndex + 1) + ' / ' + adImages.length;

    // Thumbnail highlighting
    document.querySelectorAll('.ad-thumb').forEach((thumb, i) => {
        if (i === currentImageIndex) {
            thumb.className = 'ad-thumb cursor-pointer w-20 h-16 sm:w-24 sm:h-20 object-cover rounded-lg border-2 border-brand opacity-100 shadow-sm transition';
        } else {
            thumb.className = 'ad-thumb cursor-pointer w-20 h-16 sm:w-24 sm:h-20 object-cover rounded-lg border-2 border-transparent opacity-60 hover:opacity-100 hover:border-slate-300 transition';
        }
    });
}

function changeMainImage(btn, src) {
    currentImageIndex = adImages.indexOf(src);
    if (currentImageIndex < 0) currentImageIndex = 0;
    updateImageUI();
}

function navigateImage(direction) {
    if (adImages.length <= 1) return;
    currentImageIndex = (currentImageIndex + direction + adImages.length) % adImages.length;
    updateImageUI();
}

function openFullscreen() {
    const modal   = document.getElementById('fullscreenModal');
    const fsImg   = document.getElementById('fullscreenImage');
    const mainImg = document.getElementById('mainAdImage');
    const fsCounter = document.getElementById('fsCounter');

    fsImg.src = mainImg.src;
    if (fsCounter) fsCounter.textContent = (currentImageIndex + 1) + ' / ' + adImages.length;

    // Show/hide FS nav arrows
    const fsPrev = document.getElementById('fsPrevBtn');
    const fsNext = document.getElementById('fsNextBtn');
    if (fsPrev) fsPrev.style.display = adImages.length > 1 ? 'flex' : 'none';
    if (fsNext) fsNext.style.display = adImages.length > 1 ? 'flex' : 'none';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeFullscreen() {
    const modal = document.getElementById('fullscreenModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.style.overflow = '';
}

function handleFsBackdropClick(e) {
    // Close only when clicking the backdrop (not the image or buttons)
    if (e.target === document.getElementById('fullscreenModal') ||
        e.target.id === 'fullscreenImage') {
        // clicking image = do nothing; backdrop = close
        if (e.target.id !== 'fullscreenImage') closeFullscreen();
    }
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft')  navigateImage(-1);
    if (e.key === 'ArrowRight') navigateImage(1);
    if (e.key === 'Escape')     closeFullscreen();
});

// Touch swipe on fullscreen
(function() {
    let touchStartX = 0;
    const modal = document.getElementById('fullscreenModal');
    if (!modal) return;
    modal.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
    modal.addEventListener('touchend',   e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) navigateImage(diff > 0 ? 1 : -1);
    }, {passive:true});
})();
</script>

<?php include 'includes/footer.php'; ?>
