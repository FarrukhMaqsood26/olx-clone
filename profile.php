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

<main class="flex-grow py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
    
    <!-- Profile Header -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8 flex flex-col md:flex-row items-center md:items-start gap-6 relative">
        <div class="w-24 h-24 bg-brand/10 text-brand rounded-full flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user text-4xl"></i>
        </div>
        
        <div class="flex-1 text-center md:text-left">
            <h2 class="text-2xl font-bold text-slate-900 mb-2"><?= htmlspecialchars($user['name']) ?></h2>
            <div class="flex flex-col md:flex-row gap-2 md:gap-6 text-sm text-slate-600 mb-6 font-medium">
                <span class="flex items-center justify-center md:justify-start gap-2"><i class="far fa-envelope text-slate-400"></i> <?= htmlspecialchars($user['email']) ?></span>
                <?php if($user['phone']): ?>
                    <span class="flex items-center justify-center md:justify-start gap-2"><i class="fas fa-phone text-slate-400"></i> <?= htmlspecialchars($user['phone']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 text-center">
                    <div class="text-2xl font-extrabold text-brand"><?= $totalAds ?></div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mt-1">Total Ads</div>
                </div>
                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                    <div class="text-2xl font-extrabold text-emerald-600"><?= $activeAds ?></div>
                    <div class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mt-1">Active</div>
                </div>
                <div class="bg-slate-100 border border-slate-200 rounded-xl p-4 text-center">
                    <div class="text-2xl font-extrabold text-slate-700"><?= $soldAds ?></div>
                    <div class="text-xs font-semibold text-slate-500 uppercase tracking-widest mt-1">Sold</div>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                    <div class="text-2xl font-extrabold text-blue-600"><?= $totalChats ?></div>
                    <div class="text-xs font-semibold text-blue-600 uppercase tracking-widest mt-1">Chats</div>
                </div>
            </div>
        </div>
        
        <button onclick="toggleEditForm()" class="mt-4 md:mt-0 w-full md:w-auto bg-white border-2 border-slate-200 hover:border-brand text-slate-700 hover:text-brand font-bold py-2.5 px-6 rounded-full transition shadow-sm flex items-center justify-center gap-2">
            <i class="fas fa-pen"></i> Edit Profile
        </button>
    </div>

    <!-- Edit Profile Form (hidden by default) -->
    <div id="editProfileForm" class="hidden bg-white border border-slate-200 rounded-2xl shadow-sm p-6 md:p-8 mb-8 transition-all">
        <h3 class="text-xl font-bold text-slate-900 mb-6">Edit Profile</h3>
        <form action="api/auth.php?action=update_profile" method="POST" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="03xx-xxxxxxx" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Email (cannot be changed)</label>
                    <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled class="w-full px-4 py-3 rounded-lg border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">New Password (leave blank to keep)</label>
                    <input type="password" name="new_password" placeholder="••••••••" class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 outline-none text-slate-800">
                </div>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" onclick="toggleEditForm()" class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-6 rounded-lg transition">Cancel</button>
                <button type="submit" class="bg-brand hover:bg-brand-light text-white font-bold py-2.5 px-8 rounded-lg shadow-sm transition">Save Changes</button>
            </div>
        </form>
    </div>

    <!-- My Ads Section -->
    <div class="mb-6 flex overflow-x-auto no-scrollbar gap-2 border-b border-slate-200">
        <button class="profile-tab active whitespace-nowrap pb-3 px-4 font-bold text-brand border-b-2 border-brand" onclick="filterAds('all', this)">All Ads (<?= $totalAds ?>)</button>
        <button class="profile-tab whitespace-nowrap pb-3 px-4 font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800" onclick="filterAds('active', this)">Active (<?= $activeAds ?>)</button>
        <button class="profile-tab whitespace-nowrap pb-3 px-4 font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800" onclick="filterAds('sold', this)">Sold (<?= $soldAds ?>)</button>
    </div>

    <div id="adsContainer" class="space-y-4">
        <?php if(count($myAds) > 0): ?>
            <?php foreach($myAds as $ad): ?>
                <div class="my-ad-card relative bg-white border border-slate-200 rounded-xl p-4 sm:p-6 flex flex-col sm:flex-row gap-6 transition hover:shadow-md" data-status="<?= $ad['status'] ?>">
                    
                    <a href="ad.php?id=<?= $ad['id'] ?>" class="w-full sm:w-48 h-48 sm:h-32 flex-shrink-0 rounded-lg overflow-hidden border border-slate-100">
                        <?php $img = $ad['main_image'] ?: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=300&q=80'; ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="Ad image" class="w-full h-full object-cover">
                    </a>
                    
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <h3 class="text-lg font-bold text-slate-900 leading-tight"><?= htmlspecialchars($ad['title']) ?></h3>
                                <?php
                                    $bg = 'bg-slate-100 text-slate-700';
                                    if ($ad['status'] == 'active') $bg = 'bg-emerald-100 text-emerald-800';
                                    if ($ad['status'] == 'sold') $bg = 'bg-slate-200 text-slate-600';
                                    if ($ad['status'] == 'pending') $bg = 'bg-amber-100 text-amber-800';
                                    if ($ad['status'] == 'rejected') $bg = 'bg-red-100 text-red-800';
                                ?>
                                <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-widest rounded-full <?= $bg ?>"><?= ucfirst($ad['status']) ?></span>
                            </div>
                            <div class="text-2xl font-extrabold text-brand mb-4">Rs <?= number_format($ad['price']) ?></div>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                <span class="flex items-center gap-1"><i class="fas fa-map-marker-alt text-brand"></i> <?= htmlspecialchars($ad['location']) ?></span>
                                <span class="flex items-center gap-1"><i class="far fa-clock"></i> <?= date('M d, Y', strtotime($ad['created_at'])) ?></span>
                                <?php if($ad['category_name']): ?>
                                    <span class="flex items-center gap-1"><i class="fas fa-tag"></i> <?= htmlspecialchars($ad['category_name']) ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex gap-2 w-full sm:w-auto">
                                <?php if($ad['status'] === 'active'): ?>
                                    <button class="flex-1 sm:flex-none bg-white border border-emerald-500 hover:bg-emerald-50 text-emerald-700 font-bold py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2" 
                                        onclick="confirmAction('Mark this ad as sold?', () => window.location='api/ads.php?action=mark_sold&id=<?= $ad['id'] ?>')">
                                        <i class="fas fa-check-circle"></i> Mark Sold
                                    </button>
                                <?php endif; ?>
                                <button class="flex-1 sm:flex-none bg-white border border-red-300 hover:bg-red-50 text-red-600 font-bold py-2 px-4 rounded-lg text-sm transition flex items-center justify-center gap-2" 
                                    onclick="confirmAction('Delete this ad permanently?', () => window.location='api/ads.php?action=delete&id=<?= $ad['id'] ?>')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="bg-white border border-slate-200 border-dashed rounded-2xl p-16 text-center text-slate-500 flex flex-col items-center justify-center">
                <i class="fas fa-box-open text-5xl mb-4 text-slate-300"></i>
                <h3 class="text-xl font-bold text-slate-700 mb-2">No Ads Posted Yet</h3>
                <p class="mb-6">Start selling by posting your first ad!</p>
                <a href="post-ad.php" class="bg-brand text-white font-bold px-6 py-2.5 rounded-full hover:bg-brand-light transition flex items-center gap-2">
                    <i class="fas fa-plus"></i> Post an Ad
                </a>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
function toggleEditForm() {
    const form = document.getElementById('editProfileForm');
    form.classList.toggle('hidden');
}

function filterAds(status, btn) {
    // Update tabs
    document.querySelectorAll('.profile-tab').forEach(t => {
        t.className = 'profile-tab whitespace-nowrap pb-3 px-4 font-semibold text-slate-500 border-b-2 border-transparent hover:text-slate-800';
    });
    btn.className = 'profile-tab active whitespace-nowrap pb-3 px-4 font-bold text-brand border-b-2 border-brand';
    
    // Filter ads
    document.querySelectorAll('.my-ad-card').forEach(card => {
        if (status === 'all' || card.getAttribute('data-status') === status) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function confirmAction(msg, action) {
    if(confirm(msg)) action();
}
</script>

<?php include 'includes/footer.php'; ?>
