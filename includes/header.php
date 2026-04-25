<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Count unread messages for logged-in user
$unread_count = 0;
if (isset($_SESSION['user_id'])) {
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
    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#002f34',
                            light: '#00464e',
                        },
                        accent: {
                            DEFAULT: '#3a77ff',
                            hover: '#2960da'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script>
        // Alpine-like minimal toggle for mobile menu
        function toggleMobileMenu() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-900 min-h-screen flex flex-col font-sans antialiased">

<!-- Header -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 gap-4">
            
            <!-- Mobile Menu Button & Logo -->
            <div class="flex items-center gap-3">
                <button onclick="toggleMobileMenu()" class="md:hidden text-brand hover:text-brand-light p-2 transition">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="index.php" class="text-3xl font-extrabold text-brand tracking-tight">OLX</a>
            </div>

            <!-- Search Bar (Desktop dominant, hidden on small mobile unless focused) -->
            <form action="search.php" method="GET" class="hidden sm:flex flex-1 max-w-2xl border-2 border-brand rounded overflow-hidden focus-within:ring-2 focus-within:ring-brand/20 transition-shadow">
                <select name="location" class="hidden md:block w-36 px-3 bg-white border-r border-slate-200 outline-none text-sm font-medium text-slate-700 cursor-pointer">
                    <option value="">Pakistan</option>
                    <option value="Punjab">Punjab</option>
                    <option value="Sindh">Sindh</option>
                    <option value="Islamabad">Islamabad</option>
                </select>
                <input type="text" name="q" placeholder="Find Cars, Mobile Phones and more..." autocomplete="off" class="flex-1 px-4 outline-none text-sm text-slate-800 placeholder-slate-400">
                <button type="submit" class="bg-brand text-white px-5 hover:bg-brand-light transition flexitems-center justify-center">
                    <i class="fas fa-search"></i>
                </button>
            </form>

            <!-- Desktop Actions -->
            <div class="hidden md:flex items-center gap-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/index.php" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full transition">Admin Panel</a>
                    <?php endif; ?>
                    
                    <a href="chat.php" class="text-brand hover:text-brand-light relative p-2 transition" title="Messages">
                        <i class="far fa-envelope text-xl"></i>
                        <?php if($unread_count > 0): ?>
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border-2 border-white"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <div class="relative group">
                        <a href="profile.php" class="flex items-center gap-2 font-medium text-brand hover:underline p-2">
                            <i class="far fa-user-circle text-xl"></i>
                            <span class="hidden lg:block text-sm">Hi, <?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) ?></span>
                        </a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="font-semibold text-brand text-sm hover:underline px-2">Login</a>
                <?php endif; ?>
                
                <a href="post-ad.php" class="flex items-center gap-2 bg-white border-[3px] border-white outline outline-2 outline-slate-200 rounded-full py-1.5 px-4 font-bold text-sm text-brand shadow hover:outline-brand hover:shadow-md transition">
                    <i class="fas fa-plus text-accent"></i> SELL
                </a>
            </div>
            
            <!-- Mobile Search Icon (only visible on small mobile) -->
            <button class="sm:hidden text-brand p-2">
                <i class="fas fa-search text-xl"></i>
            </button>
        </div>
    </div>
    
    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="hidden border-t border-slate-100 bg-white md:hidden">
        <div class="px-4 py-3 space-y-3">
            <form action="search.php" method="GET" class="flex border-2 border-brand rounded overflow-hidden">
                <input type="text" name="q" placeholder="Search..." class="flex-1 px-3 py-2 outline-none text-sm">
                <button type="submit" class="bg-brand text-white px-4"><i class="fas fa-search"></i></button>
            </form>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/index.php" class="block font-bold text-accent py-2">Go to Admin Panel</a>
                <?php endif; ?>
                <a href="profile.php" class="block font-medium text-slate-700 py-2 border-b border-slate-100">My Profile (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
                <a href="chat.php" class="block font-medium text-slate-700 py-2 border-b border-slate-100 flex items-center justify-between">
                    Messages 
                    <?php if($unread_count > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="api/auth.php?action=logout" class="block font-medium text-red-600 py-2">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block font-bold text-brand py-2">Login / Register</a>
            <?php endif; ?>
            <a href="post-ad.php" class="block bg-accent text-white text-center font-bold rounded-lg py-3 mt-4">
                <i class="fas fa-plus mr-2"></i> SELL AN ITEM
            </a>
        </div>
    </div>
</header>

<!-- Alerts Layout -->
<div class="max-w-7xl mx-auto px-4 mt-4 w-full">
<?php
if (isset($_GET['error'])) {
    $error_messages = [
        'invalid_credentials' => 'Invalid email or password.',
        'email_exists' => 'Email already exists.',
        'login_required' => 'Please login to continue.',
        'registration_failed' => 'Registration failed.',
        'email_not_found' => 'Email not found.',
        'invalid_token' => 'Invalid reset link.',
        'invalid_otp' => 'Incorrect OTP.',
        'already_exists' => 'Username, Email, or Phone already in use.',
    ];
    $err = isset($error_messages[$_GET['error']]) ? $error_messages[$_GET['error']] : 'An error occurred.';
    echo '<div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm font-medium"><i class="fas fa-exclamation-circle text-red-500"></i> ' . $err . '</div>';
}
if (isset($_GET['success'])) {
    $success_messages = [
        'ad_posted' => 'Ad posted successfully!',
        'ad_updated' => 'Ad updated.',
        'ad_deleted' => 'Ad deleted.',
        'profile_updated' => 'Profile updated.',
        'password_reset' => 'Password reset successfully.',
        'reset_link_sent' => 'Email Sent! <a href="' . (isset($_GET['link']) ? htmlspecialchars($_GET['link']) : '#') . '" class="underline font-bold text-brand hover:text-brand-light ml-1">Click here to test resetting</a>',
        'account_verified' => 'Account verified!'
    ];
    $msg = isset($success_messages[$_GET['success']]) ? $success_messages[$_GET['success']] : 'Success.';
    echo '<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm font-medium"><i class="fas fa-check-circle text-green-500"></i> ' . $msg . '</div>';
}
?>
</div>
