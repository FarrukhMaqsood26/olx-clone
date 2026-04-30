<?php
require_once '../includes/config.php';
require_once 'includes/header.php';

// Fetch quick stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalAds = $pdo->query("SELECT COUNT(*) FROM ads")->fetchColumn();
$activeAds = $pdo->query("SELECT COUNT(*) FROM ads WHERE status = 'active'")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();


// Fetch recent users
$recentUsers = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Fetch recent ads
$recentAds = $pdo->query("SELECT a.id, a.title, a.status, a.price, u.name as user_name FROM ads a JOIN users u ON a.user_id = u.id ORDER BY a.created_at DESC LIMIT 5")->fetchAll();
?>

<div class="mb-6">
    <h2 class="text-2xl font-bold text-slate-800">Dashboard Overview</h2>
    <p class="text-slate-500 mt-1">Snapshot of the marketplace activity.</p>
</div>

<!-- Stats Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between group hover:border-brand transition">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total Users</p>
            <h3 class="text-3xl font-extrabold text-slate-800"><?= $totalUsers ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-500 flex items-center justify-center text-xl group-hover:bg-blue-500 group-hover:text-white transition">
            <i class="fas fa-users"></i>
        </div>
    </div>
    
    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between group hover:border-brand transition">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Total Ads</p>
            <h3 class="text-3xl font-extrabold text-slate-800"><?= $totalAds ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-indigo-50 text-indigo-500 flex items-center justify-center text-xl group-hover:bg-indigo-500 group-hover:text-white transition">
            <i class="fas fa-bullhorn"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between group hover:border-brand transition">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Active Ads</p>
            <h3 class="text-3xl font-extrabold text-slate-800"><?= $activeAds ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-xl group-hover:bg-emerald-500 group-hover:text-white transition">
            <i class="fas fa-check-circle"></i>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex items-center justify-between group hover:border-brand transition">
        <div>
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">Categories</p>
            <h3 class="text-3xl font-extrabold text-slate-800"><?= $totalCategories ?></h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-amber-50 text-amber-500 flex items-center justify-center text-xl group-hover:bg-amber-500 group-hover:text-white transition">
            <i class="fas fa-tags"></i>
        </div>
    </div>


</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    <!-- Recent Users -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Recent Users</h3>
            <a href="users.php" class="text-xs font-bold text-accent hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Role</th>
                        <th class="px-6 py-3">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($recentUsers as $u): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-800"><?= htmlspecialchars($u['name']) ?></td>
                        <td class="px-6 py-4">
                            <?php if($u['role'] == 'admin'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-brand text-white text-[10px] font-bold uppercase tracking-wider">Admin</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider">User</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Ads -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col">
        <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Recent Ads</h3>
            <a href="ads.php" class="text-xs font-bold text-accent hover:underline">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                    <tr>
                        <th class="px-6 py-3">Title</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($recentAds as $a): ?>
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800 truncate max-w-[150px]" title="<?= htmlspecialchars($a['title']) ?>"><?= htmlspecialchars($a['title']) ?></div>
                            <div class="text-xs text-slate-400 mt-1">by <?= htmlspecialchars($a['user_name']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <?php
                                $bg = 'bg-slate-100 text-slate-600';
                                if ($a['status'] == 'active') $bg = 'bg-emerald-100 text-emerald-800';
                                if ($a['status'] == 'pending') $bg = 'bg-amber-100 text-amber-800';
                                if ($a['status'] == 'rejected') $bg = 'bg-red-100 text-red-800';
                            ?>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?= $bg ?>"><?= ucfirst($a['status']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-800 whitespace-nowrap">Rs <?= number_format($a['price']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
