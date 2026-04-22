<?php include 'includes/header.php'; ?>

<main>
    <div class="auth-container glass-panel">
        <h2>Welcome to OLX</h2>
        <form class="auth-form" action="api/auth.php" method="POST">
            <input type="hidden" name="login" value="1">
            <div class="input-group">
                <label>Email, Username, or Phone</label>
                <input type="text" name="identifier" required placeholder="Enter your email, username, or phone" id="login-identifier">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password" id="login-password">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 10px;">
                    <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer;">
                        <input type="checkbox" name="remember_me" id="remember-me"> Remember Me
                    </label>
                    <a href="forgot-password.php" style="font-size: 13px; color: var(--accent-blue); text-decoration: none;">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" class="btn-auth" id="login-submit">Log In</button>
            <div class="divider"><span>OR</span></div>
            <a href="api/auth.php?action=google_login" class="btn-google" id="google-login">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                Continue with Google
            </a>
        </form>
        
        <div class="auth-links">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
