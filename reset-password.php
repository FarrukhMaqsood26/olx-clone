<?php 
$token = isset($_GET['token']) ? htmlspecialchars($_GET['token']) : '';
if (!$token) {
    header("Location: login.php");
    exit;
}

include 'includes/header.php'; 
?>

<main>
    <div class="auth-container glass-panel">
        <h2>Reset Password</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            Enter a new password for your account.
        </p>
        <form class="auth-form" action="api/auth.php" method="POST">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="token" value="<?= $token ?>">
            
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="Create new password" minlength="6" id="reset-password-input">
            </div>
            
            <button type="submit" class="btn-auth" id="reset-submit">Update Password</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
