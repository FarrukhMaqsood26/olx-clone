<?php 
require_once 'includes/config.php';
$token = $_GET['token'] ?? '';

// Verify token before showing form
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
$stmt->execute([$token]);
$valid = $stmt->fetch();

if (!$valid && !isset($_GET['success'])) {
    header("Location: forgot-password.php?error=invalid_token");
    exit;
}

include 'includes/header.php'; 
?>

<main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">New Password</h2>
            <p class="text-slate-500">Please enter your new password below.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                <input type="password" name="password" required minlength="8" placeholder="••••••••" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Confirm New Password</label>
                <input type="password" required minlength="8" placeholder="••••••••" id="confirm_password"
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <button type="submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Update Password
            </button>
        </form>
    </div>
</main>

<script>
document.querySelector('form').onsubmit = function(e) {
    const p1 = document.querySelector('input[name="password"]').value;
    const p2 = document.getElementById('confirm_password').value;
    if (p1 !== p2) {
        alert("Passwords do not match!");
        return false;
    }
};
</script>

<?php include 'includes/footer.php'; ?>
