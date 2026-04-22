<?php 
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
if (!$email) {
    header("Location: signup.php");
    exit;
}

include 'includes/header.php'; 
?>

<main>
    <div class="auth-container glass-panel">
        <h2>Verify Phone Number</h2>
        <p style="text-align: center; color: var(--text-secondary); margin-bottom: 20px;">
            We've sent a 4-digit code to your phone number. Please enter it below.
        </p>
        <form class="auth-form" action="api/auth.php" method="POST">
            <input type="hidden" name="action" value="verify_otp">
            <input type="hidden" name="email" value="<?= $email ?>">
            
            <div class="input-group">
                <label>4-Digit OTP Code</label>
                <input type="text" name="otp" required placeholder="e.g. 1234" maxlength="4" pattern="\d{4}" style="text-align:center; font-size:24px; letter-spacing:4px;" id="verify-otp-input">
            </div>
            
            <button type="submit" class="btn-auth" id="verify-submit">Verify Account</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
