<?php include 'includes/header.php'; ?>

<div class="flex-grow flex items-center justify-center py-12">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-extrabold text-slate-900 mb-2">Verify OTP</h2>
            <p class="text-slate-500">We've sent a 6-digit code to <strong><?= htmlspecialchars($_GET['email'] ?? '') ?></strong></p>
        </div>
        
        <form action="api/auth.php?action=verify_otp" method="POST" class="space-y-6">
            <input type="hidden" name="email" value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
            
            <div>
                <label for="otp" class="block text-sm font-medium text-slate-700 mb-2">Enter 6-Digit Code</label>
                <input type="text" name="otp" id="otp" required maxlength="6" pattern="\d{6}" placeholder="000000" 
                    class="w-full px-4 py-4 text-center text-2xl tracking-[1em] rounded-lg border border-slate-300 focus:ring-2 focus:ring-brand/20 focus:border-brand outline-none transition text-slate-800 font-bold">
            </div>
            
            <button type="submit" class="w-full bg-brand hover:bg-brand-light text-white font-bold py-3.5 px-4 rounded-lg shadow-sm transition">
                Verify Account
            </button>

            <?php if (isset($_GET['simulated_otp'])): ?>
                <div class="mt-6 p-4 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    <p class="font-bold mb-1"><i class="fas fa-info-circle mr-2"></i> Local Development Info:</p>
                    <p>Usually, this goes to your email. For testing, use code: <strong><?= htmlspecialchars($_GET['simulated_otp']) ?></strong></p>
                </div>
            <?php endif; ?>
        </form>
        
        <p class="mt-8 text-center text-sm text-slate-500">
            Didn't receive code? <a href="#" class="font-bold text-brand hover:underline">Resend OTP</a>
        </p>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
