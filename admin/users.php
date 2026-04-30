<?php
require_once '../includes/config.php';
require_once 'includes/header.php';

$users = $pdo->query("
    SELECT u.*
    FROM users u
    ORDER BY u.created_at DESC
")->fetchAll();
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
                            <?php 
                                $avatar_path = $user['avatar'];
                                if (empty($avatar_path) || $avatar_path == 'default.png') {
                                    $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($user['name']) . "&background=random&color=fff";
                                } else if (strpos($avatar_path, 'http') === 0) {
                                    $avatar_url = $avatar_path;
                                } else {
                                    $avatar_url = "../uploads/avatars/" . $avatar_path;
                                }
                            ?>
                            <img src="<?= $avatar_url ?>" alt="Avatar" class="w-10 h-10 rounded-full object-cover border border-slate-200" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['name']) ?>&background=random&color=fff'">
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
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-slate-500">
                        <?= date('M d, Y', strtotime($user['created_at'])) ?>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <!-- View Face Scan Button -->
                            <?php 
                                $scanStmt = $pdo->prepare("SELECT COUNT(*) FROM user_face_scans WHERE user_id = ?");
                                $scanStmt->execute([$user['id']]);
                                $scanCount = $scanStmt->fetchColumn();
                                $hasScan = $scanCount > 0;
                            ?>
                            <button type="button" 
                                    onclick="viewFaceScan(<?= $user['id'] ?>, '<?= htmlspecialchars($user['name']) ?>')" 
                                    class="w-8 h-8 rounded-full transition flex items-center justify-center shadow-sm <?= $hasScan ? 'bg-emerald-500 text-white hover:bg-emerald-600' : 'bg-slate-100 text-slate-300 hover:bg-slate-200' ?>" 
                                    title="<?= $hasScan ? "View $scanCount Face Scans" : 'No scan data available' ?>">
                                <i class="fas <?= $hasScan ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                            </button>

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

<!-- Face Scan Modal -->
<div id="faceScanModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl flex flex-col">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <div>
                <h3 class="text-xl font-bold text-slate-800" id="modalUserName">User Face Scan</h3>
                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mt-1">Biometric 3D Identity Report</p>
            </div>
            <button onclick="closeFaceScanModal()" class="w-10 h-10 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-600 transition flex items-center justify-center">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-8 bg-white">
            <div id="scanContent" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Data will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
function viewFaceScan(userId, userName) {
    const modal = document.getElementById('faceScanModal');
    const content = document.getElementById('scanContent');
    const title = document.getElementById('modalUserName');
    
    title.innerText = userName + "'s Biometric Scan";
    content.innerHTML = '<div class="col-span-full py-20 text-center text-slate-400"><i class="fas fa-circle-notch animate-spin text-3xl mb-4"></i><p class="font-medium">Loading 3D verification data...</p></div>';
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    // Use jQuery only for the AJAX part
    $.ajax({
        url: 'get_user_scans.php',
        type: 'GET',
        data: { user_id: userId },
        success: function(response) {
            content.innerHTML = response;
        },
        error: function() {
            content.innerHTML = '<div class="col-span-full py-20 text-center text-red-500"><i class="fas fa-exclamation-circle text-3xl mb-4"></i><p>Failed to load biometric data.</p></div>';
        }
    });
}

function closeFaceScanModal() {
    const modal = document.getElementById('faceScanModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function toggleLandmarks(id) {
    const el = document.getElementById('landmarks-' + id);
    if (!el) return;
    
    if (el.classList.contains('hidden')) {
        el.classList.remove('hidden');
        // Scroll to the JSON if it's large
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        el.classList.add('hidden');
    }
}

function copyToClipboard(id) {
    const text = document.getElementById(id).innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('Biometric JSON copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
