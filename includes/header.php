<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Count unread messages for logged-in user
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
    // We need config for $pdo, but config may have already been loaded
    if (!isset($pdo)) {
        require_once __DIR__ . '/config.php';
    }
    try {
        $badge_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $badge_stmt->execute([$_SESSION['user_id']]);
        $unread_count = $badge_stmt->fetchColumn();
    } catch(PDOException $e) {
        $unread_count = 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="OLX Clone - Buy and sell anything near you. Find the best deals on mobiles, vehicles, property, and more.">
    <title>OLX Clone - Buy and Sell Anything</title>
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🛒</text></svg>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="js/app.js"></script>
</head>
<body>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<header class="glass-panel">
    <div class="nav-back-fwd">
        <button onclick="history.back()" title="Go Back"><i class="fas fa-arrow-left"></i></button>
        <button onclick="history.forward()" title="Go Forward"><i class="fas fa-arrow-right"></i></button>
        <a href="index.php" class="logo">OLX</a>
    </div>
    
    <form action="search.php" method="GET" class="search-bar glass-panel" id="mainSearchForm">
        <select name="location" id="location-select">
            <option value="">Pakistan</option>
            <option value="Punjab">Punjab</option>
            <option value="Sindh">Sindh</option>
            <option value="Islamabad">Islamabad</option>
            <option value="Lahore">Lahore</option>
            <option value="Karachi">Karachi</option>
            <option value="Rawalpindi">Rawalpindi</option>
        </select>
        <input type="text" name="q" placeholder="Find Cars, Mobile Phones and more..." autocomplete="off">
        <button type="submit"><i class="fas fa-search"></i></button>
    </form>

    <div class="header-actions">
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="chat.php" class="login-link" title="Messages" style="position:relative;">
                <i class="far fa-envelope" style="font-size:18px;"></i>
                <?php if($unread_count > 0): ?>
                    <span class="msg-badge"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                <?php endif; ?>
            </a>
            <a href="profile.php" class="login-link" title="My Account"><i class="far fa-user-circle" style="font-size:18px;"></i></a>
            <span class="header-username">Hi, <?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) ?></span>
            <a href="api/auth.php?action=logout" class="login-link" style="color: var(--danger); font-size: 14px;" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
        <?php else: ?>
            <a href="login.php" class="login-link">Login</a>
        <?php endif; ?>
        <a href="post-ad.php" class="btn-sell"><i class="fas fa-plus"></i> <span class="sell-text">SELL</span></a>
    </div>
</header>

<?php
// Simulate SMS notification
if (isset($_GET['simulated_otp'])) {
    echo '<div class="alert-banner" style="background:var(--accent-purple); color:white; font-weight:bold; z-index:9999; margin-top:20px; box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);">
        <i class="fas fa-sms"></i> NEW SMS: Your OLX clone verification code is ' . htmlspecialchars($_GET['simulated_otp']) . '
    </div>';
}

// Show flash messages from URL params
if (isset($_GET['error'])) {
    $error_messages = [
        'invalid_credentials' => 'Invalid email or password. Please try again.',
        'email_exists' => 'An account with this email already exists.',
        'login_required' => 'Please login to continue.',
        'registration_failed' => 'Registration failed. Please try again.',
        'email_not_found' => 'Email address not found in our records.',
        'invalid_token' => 'Invalid or expired password reset link.',
        'invalid_otp' => 'The OTP code is incorrect. Try again.',
    ];
    $err = isset($error_messages[$_GET['error']]) ? $error_messages[$_GET['error']] : 'An error occurred.';
    echo '<div class="alert-banner alert-error"><i class="fas fa-exclamation-circle"></i> ' . $err . '</div>';
}
if (isset($_GET['success'])) {
    $success_messages = [
        'ad_posted' => 'Your ad has been posted successfully!',
        'ad_updated' => 'Ad updated successfully.',
        'ad_deleted' => 'Ad has been deleted.',
        'profile_updated' => 'Profile updated successfully.',
        'password_reset' => 'Password reset successfully. You can now login.',
        'reset_link_sent' => 'Email Sent! <a href="' . (isset($_GET['link']) ? htmlspecialchars($_GET['link']) : '#') . '" style="color:var(--primary-teal); font-weight:bold; text-decoration:underline; font-size:16px;">Click here to reset password</a>',
        'account_verified' => 'Phone number verified! Account activated successfully.'
    ];
    $msg = isset($success_messages[$_GET['success']]) ? $success_messages[$_GET['success']] : 'Operation completed successfully.';
    echo '<div class="alert-banner alert-success" style="z-index:9999;"><i class="fas fa-check-circle"></i> ' . $msg . '</div>';
}
?>
