<?php
require_once 'includes/config.php';
// Redirect to index if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$simulated_token = isset($_GET['simulated_token']) ? htmlspecialchars($_GET['simulated_token']) : '';

include 'includes/header.php';
?>

<div class="flex-grow flex items-center justify-center p-4">
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm max-w-md w-full p-8 text-center relative overflow-hidden">
        <!-- Decorative bg -->
        <div class="absolute -top-24 -right-24 w-48 h-48 bg-brand/5 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-accent/5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10">
            <div class="w-20 h-20 bg-brand/5 text-brand rounded-full flex items-center justify-center text-4xl mx-auto mb-6">
                <i class="fas fa-envelope-open-text"></i>
            </div>
            
            <h2 class="text-2xl font-bold text-slate-800 mb-2">Verify Your Email</h2>
            
            <?php if ($email): ?>
                <p class="text-slate-500 mb-6 text-sm">We've sent a verification link to<br><span class="font-bold text-slate-800"><?= $email ?></span></p>
                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 mb-6">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2"><i class="fas fa-info-circle text-accent"></i> Local Testing Mode</p>
                    <p class="text-sm text-slate-700 mb-3">Since this is a local environment without email delivery configured, click the simulated link below to verify your account.</p>
                    <a href="api/auth.php?action=verify_email&token=<?= $simulated_token ?>" class="inline-block bg-accent hover:bg-accent-hover text-white font-bold py-2.5 px-6 rounded-lg shadow-sm transition">
                        Verify Account
                    </a>
                </div>
                <p class="text-xs text-slate-400">In a production environment, this link would be emailed to the user.</p>
            <?php else: ?>
                <p class="text-slate-500 mb-6 text-sm">Please check your email for the verification link.</p>
            <?php endif; ?>
            
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-sm font-semibold text-slate-600">Back to <a href="login.php" class="text-accent hover:underline">Login</a></p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
