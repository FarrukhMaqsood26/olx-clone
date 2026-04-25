<?php 
require_once 'includes/config.php';
if (isset($_SESSION['user_id'])) {
    $role = isset($_SESSION['user_role']) ? strtolower(trim($_SESSION['user_role'])) : 'user';
    if ($role === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
include 'includes/header.php'; 
?>

<main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Welcome Back</h2>
            <p class="text-slate-500">Log in to manage your ads and messages.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="login" value="1">
            
            <div>
                <label for="login-identifier" class="block text-sm font-medium text-slate-700 mb-2">Email, Username, or Phone</label>
                <input type="text" name="identifier" id="login-identifier" required placeholder="Enter your detail" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <div>
                <label for="login-password" class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                <input type="password" name="password" id="login-password" required placeholder="Enter your password" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
                
                <div class="flex items-center justify-between mt-4">
                    <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember_me" id="remember-me" class="rounded text-brand focus:ring-brand">
                        Remember Me
                    </label>
                    <a href="forgot-password.php" class="text-sm font-semibold text-accent hover:underline">Forgot Password?</a>
                </div>
            </div>
            
            <button type="submit" id="login-submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Log In
            </button>
            
            <div class="relative flex items-center py-2">
                <div class="flex-grow border-t border-slate-200"></div>
                <span class="flex-shrink-0 mx-4 text-slate-400 text-sm font-medium uppercase tracking-wider">or sign in with</span>
                <div class="flex-grow border-t border-slate-200"></div>
            </div>
            
            <a href="api/auth.php?action=google_login" id="google-login" class="w-full flex items-center justify-center gap-3 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold py-3 px-4 rounded-lg shadow-sm transition">
                <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
                Google
            </a>
        </form>
        
        <p class="mt-8 text-center text-sm text-slate-500">
            Don't have an account? <a href="signup.php" class="font-bold text-brand hover:underline">Sign up</a>
        </p>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
