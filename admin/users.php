<?php
require_once '../includes/config.php';
require_once 'includes/header.php';

$users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
?>

<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-bold text-slate-800">User Management</h2>
        <p class="text-slate-500 mt-1">Manage system accounts and roles.</p>
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">User Details</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Status / Role</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach($users as $user): ?>
                <tr class="hover:bg-slate-50 transition group">
                    <td class="px-6 py-4 font-mono text-xs text-slate-400">#<?= $user['id'] ?></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <img src="../<?php echo strpos($user['avatar'], 'http') === 0 ? $user['avatar'] : 'images/avatars/' . ($user['avatar'] ?: 'default.png'); ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200" onerror="this.src='../images/default.png'">
                            <span class="font-bold text-slate-800"><?= htmlspecialchars($user['name']) ?></span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-slate-800 font-medium"><?= htmlspecialchars($user['email']) ?></div>
                        <div class="text-xs mt-0.5 <?= $user['phone'] ? 'text-slate-500' : 'text-slate-300 italic' ?>"><?= $user['phone'] ? htmlspecialchars($user['phone']) : 'No phone provided' ?></div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-col items-start gap-2">
                            <?php if($user['role'] == 'admin'): ?>
                                <span class="px-2.5 py-1 rounded-full bg-brand text-white text-[10px] font-bold uppercase tracking-wider">Admin</span>
                            <?php else: ?>
                                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-wider">User</span>
                            <?php endif; ?>
                            
                            <?php if($user['is_phone_verified']): ?>
                                <span class="text-xs font-semibold text-emerald-500 flex items-center gap-1"><i class="fas fa-check-circle"></i> Verified</span>
                            <?php else: ?>
                                <span class="text-xs font-medium text-slate-400 flex items-center gap-1"><i class="far fa-clock"></i> Unverified</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <?php if($user['id'] !== $_SESSION['user_id']): ?>
                                <form action="../api/admin_actions.php?action=update_user_role" method="POST" class="inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <input type="hidden" name="role" value="<?= $user['role'] == 'admin' ? 'user' : 'admin' ?>">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-brand text-slate-500 hover:text-white transition flex items-center justify-center shadow-sm" title="Toggle Role">
                                        <i class="fas fa-user-shield"></i>
                                    </button>
                                </form>
                                <form action="../api/admin_actions.php?action=delete_user" method="POST" onsubmit="return confirm('WARNING: This will delete the user and ALL their ads/data. Are you sure?');" class="inline">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <button type="submit" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-red-500 text-slate-500 hover:text-white transition flex items-center justify-center shadow-sm" title="Delete User">
                                        <i class="far fa-trash-alt"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs font-bold text-slate-300 uppercase tracking-widest bg-slate-100 px-3 py-1 rounded-full">You</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
