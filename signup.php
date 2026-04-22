<?php include 'includes/header.php'; ?>

<main>
    <div class="auth-container glass-panel">
        <h2>Create an Account</h2>
        <form class="auth-form" action="api/auth.php" method="POST">
            <input type="hidden" name="signup" value="1">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" name="name" required placeholder="Enter your full name" id="signup-name">
            </div>
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="Enter your email" id="signup-email">
            </div>
            <div class="input-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" required placeholder="e.g. +92 3XX XXXXXXX" id="signup-phone">
            </div>
            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Create a password" id="signup-password" minlength="6">
            </div>
            
            <button type="submit" class="btn-auth" id="signup-submit">Sign Up</button>
            <div class="divider"><span>OR</span></div>
            <a href="api/auth.php?action=google_login" class="btn-google" id="google-signup">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                Sign up with Google
            </a>
        </form>
        
        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Log in</a></p>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
