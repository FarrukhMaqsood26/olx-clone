<?php include 'includes/header.php'; ?>

<div class="flex items-center justify-center py-12">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Reset Password</h2>
            <p class="text-slate-500">Enter your email and we'll send a reset link.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="forgot_password">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">Email Address</label>
                <input type="email" name="email" required placeholder="Enter your registered email" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <button type="submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Send Reset Link
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="login.php" class="text-sm font-semibold text-accent hover:underline flex items-center justify-center gap-1">
                <i class="fas fa-arrow-left"></i> Back to Login
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
