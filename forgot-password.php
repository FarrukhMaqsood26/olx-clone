<?php include 'includes/header.php'; ?>

<main>
    <div class="auth-container glass-panel">
        <h2>Forgot Password</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            Enter your email address and we'll send you a link to reset your password.
        </p>
        <form class="auth-form" action="api/auth.php" method="POST">
            <input type="hidden" name="action" value="forgot_password">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email" id="forgot-email">
            </div>
            
            <button type="submit" class="btn-auth" id="forgot-submit">Send Reset Link</button>
        </form>
        
        <div class="auth-links">
            <p>Remember your password? <a href="login.php">Log in</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
