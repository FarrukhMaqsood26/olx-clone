<?php include 'includes/header.php'; ?>

<main class="flex-grow flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Create New Password</h2>
            <p class="text-slate-500">Please enter your new password below.</p>
        </div>
        
        <form action="api/auth.php" method="POST" class="space-y-6">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="token" value="<?php echo isset($_GET['token']) ? htmlspecialchars($_GET['token']) : ''; ?>">
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-2">New Password</label>
                <input type="password" name="new_password" required minlength="6" placeholder="Create new password" 
                    class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800">
            </div>
            
            <button type="submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Save & Login
            </button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
