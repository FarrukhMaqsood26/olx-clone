<?php
require_once '../includes/config.php';
require_once 'includes/header.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$query = "SELECT a.*, u.name as user_name, c.name as category_name 
          FROM ads a 
          JOIN users u ON a.user_id = u.id 
          JOIN categories c ON a.category_id = c.id";

if ($status_filter) {
    $query .= " WHERE a.status = :status";
}
$query .= " ORDER BY a.created_at DESC";

$stmt = $pdo->prepare($query);
if ($status_filter) {
    $stmt->execute(['status' => $status_filter]);
} else {
    $stmt->execute();
}
$ads = $stmt->fetchAll();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">Manage Ads</h2>
        <p class="text-slate-500 mt-1">Review and moderate user listings.</p>
    </div>
    
    <div class="flex gap-2 bg-white border border-slate-200 p-1 rounded-xl shadow-sm self-start sm:self-auto">
        <a href="ads.php" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $status_filter == '' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">All</a>
        <a href="ads.php?status=pending" class="px-4 py-2 rounded-lg text-sm font-bold transition flex items-center gap-2 <?= $status_filter == 'pending' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">
            <?php if($status_filter == 'pending'): ?><span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span><?php endif; ?> Pending
        </a>
        <a href="ads.php?status=active" class="px-4 py-2 rounded-lg text-sm font-bold transition <?= $status_filter == 'active' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?>">Active</a>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">AD ID</th>
                    <th class="px-6 py-4">Title & Seller</th>
                    <th class="px-6 py-4">Category</th>
                    <th class="px-6 py-4">Price</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($ads as $ad): ?>
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-6 py-4 font-mono text-xs text-slate-400">#<?= $ad['id'] ?></td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-slate-800 mb-1 max-w-[200px] truncate" title="<?= htmlspecialchars($ad['title']) ?>"><?= htmlspecialchars($ad['title']) ?></div>
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wide">By <?= htmlspecialchars($ad['user_name']) ?> &bull; <?= date('M d, Y', strtotime($ad['created_at'])) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-block px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-xs font-semibold"><?= htmlspecialchars($ad['category_name']) ?></span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-extrabold text-slate-800">Rs <?= number_format($ad['price']) ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <?php
                            $bg = 'bg-slate-100 text-slate-600 border-slate-200';
                            if ($ad['status'] == 'active') $bg = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                            if ($ad['status'] == 'sold') $bg = 'bg-purple-50 text-purple-700 border-purple-200';
                            if ($ad['status'] == 'pending') $bg = 'bg-amber-50 text-amber-700 border-amber-200';
                            if ($ad['status'] == 'rejected') $bg = 'bg-red-50 text-red-700 border-red-200';
                        ?>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border <?= $bg ?>"><?= ucfirst($ad['status']) ?></span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="../ad.php?id=<?= $ad['id'] ?>" target="_blank" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-800 text-slate-500 hover:text-white transition flex items-center justify-center shadow-sm" title="View Ad">
                                <i class="far fa-eye"></i>
                            </a>
                            
                            <form action="../api/admin_actions.php?action=update_ad_status" method="POST" class="inline">
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                <select name="status" onchange="this.form.submit()" class="h-8 pl-2 pr-6 rounded-lg border border-slate-300 text-xs font-bold text-slate-700 outline-none focus:ring-2 focus:ring-accent/20 cursor-pointer bg-white">
                                    <option value="active" <?= $ad['status']=='active'?'selected':'' ?>>Active</option>
                                    <option value="pending" <?= $ad['status']=='pending'?'selected':'' ?>>Pending</option>
                                    <option value="rejected" <?= $ad['status']=='rejected'?'selected':'' ?>>Rejected</option>
                                    <option value="sold" <?= $ad['status']=='sold'?'selected':'' ?>>Sold</option>
                                </select>
                            </form>
                            
                            <form action="../api/admin_actions.php?action=delete_ad" method="POST" onsubmit="return confirm('Are you sure you want to completely delete this ad?');" class="inline">
                                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                                <button type="submit" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-red-500 text-slate-500 hover:text-white transition flex items-center justify-center shadow-sm" title="Delete Ad">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($ads)): ?>
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                        <i class="fas fa-inbox text-4xl mb-3 block opacity-50"></i>
                        <p class="font-medium">No ads found matching this filter.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
