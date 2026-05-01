<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Count unread messages and load avatar for logged-in user
$unread_count = 0;
$header_avatar = null;
if (isset($_SESSION['user_id'])) {
    if (!isset($pdo)) {
        require_once __DIR__ . '/config.php';
    }
    try {
        $badge_stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
        $badge_stmt->execute([$_SESSION['user_id']]);
        $unread_count = $badge_stmt->fetchColumn();

        $avatar_stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
        $avatar_stmt->execute([$_SESSION['user_id']]);
        $av = $avatar_stmt->fetchColumn();
        if ($av && $av !== 'default.png') {
            // Use a relative path that works from root and subfolders
            $header_avatar = (file_exists('uploads/avatars/' . $av) ? '' : '../') . 'uploads/avatars/' . htmlspecialchars($av);
        }
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
    <meta name="description" content="Bazaar - Buy and sell anything near you. Find the best deals on mobiles, vehicles, property, and more.">
    <title>Bazaar - Buy and Sell Anything</title>
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
    <style>
        /* ===== GLOBAL BACKGROUND TEXTURE ===== */
        body {
            background-color: #f1f5f9;
            background-image: radial-gradient(circle, #cbd5e1 1px, transparent 1px);
            background-size: 24px 24px;
        }

        /* Shared page container for consistent horizontal margins */
        .app-container {
            width: 100%;
            max-width: 88rem;
            margin-left: auto;
            margin-right: auto;
            padding-left: clamp(0.9rem, 2.4vw, 2.75rem);
            padding-right: clamp(0.9rem, 2.4vw, 2.75rem);
        }

        /* ===== HORIZONTAL SLIDE ROW ===== */
        .slide-row-wrapper { position: relative; margin: 0; }
        .slide-row {
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 24px 4px; /* Pad top/bottom for shadow, slight horizontal for safety */
            scrollbar-width: none;
        }
        .slide-row::-webkit-scrollbar { display: none; }
        .slide-row > * { flex: 0 0 calc(25% - 12px); min-width: 220px; max-width: 300px; }
        @media (max-width: 1024px) { .slide-row > * { flex: 0 0 calc(33.33% - 12px); } }
        @media (max-width: 768px)  { .slide-row > * { flex: 0 0 calc(50% - 10px); } }
        @media (max-width: 480px)  { .slide-row > * { flex: 0 0 calc(85% - 8px); } }

        /* Slide arrow buttons */
        .slide-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: white;
            border: 2px solid #e2e8f0;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #002f34;
            transition: all 0.2s ease;
        }
        .slide-btn:hover { background: #002f34; color: white; border-color: #002f34; }
        .slide-btn.prev-btn { left: -10px; }
        .slide-btn.next-btn { right: -10px; }
        .slide-btn:disabled { opacity: 0.3; cursor: not-allowed; }

        @media (max-width: 640px) {
            .slide-btn { display: none !important; }
        }

        /* ===== AD DETAIL PAGE — image nav arrows always visible ===== */
        .ad-img-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 20;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(0,0,0,0.55);
            border: 2px solid rgba(255,255,255,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: background 0.2s;
        }
        .ad-img-nav-btn:hover { background: rgba(0,0,0,0.82); }
        .ad-img-nav-btn.prev { left: 12px; }
        .ad-img-nav-btn.next { right: 12px; }

        /* Fullscreen modal arrows */
        .fs-nav-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            font-size: 1.4rem;
            transition: background 0.2s;
            z-index: 70;
        }
        .fs-nav-btn:hover { background: rgba(255,255,255,0.25); }
        .fs-nav-btn.prev { left: 20px; }
        .fs-nav-btn.next { right: 20px; }

        /* Image counter pill */
        .img-counter {
            position: absolute;
            bottom: 12px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0,0,0,0.6);
            color: white;
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 99px;
            z-index: 20;
            letter-spacing: 0.05em;
        }

        /* Zoom icon overlay */
        .zoom-icon-overlay {
            position: absolute;
            bottom: 12px;
            right: 12px;
            background: rgba(0,0,0,0.55);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 20;
            transition: background 0.2s;
        }
        .zoom-icon-overlay:hover { background: rgba(0,0,0,0.8); }

        /* Swipe hint animation */
        @keyframes swipeHint {
            0%   { transform: translateX(0); }
            30%  { transform: translateX(8px); }
            60%  { transform: translateX(-4px); }
            100% { transform: translateX(0); }
        }
    </style>
    <script>
        // Alpine-like minimal toggle for mobile menu
        function toggleMobileMenu() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        }
    </script>
</head>
<body class="text-slate-900 min-h-screen flex flex-col font-sans antialiased w-full overflow-x-hidden">

<!-- ===== FLOATING HEADER ===== -->
<header id="site-header" class="fixed top-3 sm:top-6 left-0 right-0 mx-auto z-50 app-container transition-all duration-300">
    <div class="bg-white/45 backdrop-blur-2xl border border-white/80 shadow-[0_8px_32px_rgba(0,0,0,0.1)] rounded-[1.2rem] sm:rounded-[2rem] px-4 sm:px-8 md:px-12 lg:px-16">
        <div class="flex items-center justify-between h-14 sm:h-20 gap-3 sm:gap-6">

            <!-- Mobile Menu Button & Logo -->
            <div class="flex items-center gap-1 sm:gap-3 shrink-0">
                <button onclick="toggleMobileMenu()" class="md:hidden text-brand hover:text-brand-light p-1 sm:p-2 transition rounded-lg hover:bg-slate-100 flex-shrink-0">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <a href="index.php" class="flex items-center gap-1 sm:gap-1.5 flex-shrink-0">
                    <span class="text-xl sm:text-3xl font-extrabold text-brand tracking-tight">BAZAAR</span>
                    <span class="hidden sm:block text-[10px] sm:text-xs font-bold text-white bg-accent px-1.5 sm:px-2 py-0.5 rounded-md tracking-wider">PK</span>
                </a>
            </div>

            <!-- Search Bar -->
            <form action="search.php" method="GET" class="hidden sm:flex flex-1 min-w-0 max-w-2xl border border-slate-200 bg-slate-50/80 rounded-xl overflow-hidden focus-within:ring-2 focus-within:ring-accent/30 focus-within:border-accent transition-all">
                <select name="location" class="hidden md:block w-28 px-3 bg-transparent border-r border-slate-200 outline-none text-xs font-semibold text-slate-600 cursor-pointer shrink-0">
                    <option value="">Pakistan</option>
                    <option value="Punjab">Punjab</option>
                    <option value="Sindh">Sindh</option>
                    <option value="Islamabad">Islamabad</option>
                </select>
                <input type="text" name="q" placeholder="Search cars, mobiles..." autocomplete="off"
                       class="flex-1 min-w-0 px-3 py-2 outline-none text-sm text-slate-800 placeholder-slate-400 bg-transparent">
                <button type="submit" class="bg-brand text-white px-4 hover:bg-brand-light transition flex justify-center items-center shrink-0">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </form>

            <!-- Desktop Actions -->
            <div class="hidden md:flex items-center gap-2 shrink-0">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <a href="admin/index.php" class="bg-accent hover:bg-accent-hover text-white text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-full transition">Admin</a>
                    <?php endif; ?>

                    <a href="favorites.php" class="text-slate-500 hover:text-red-500 relative p-2 transition rounded-xl hover:bg-slate-100" title="Favorites">
                        <i class="far fa-heart text-lg"></i>
                    </a>

                    <a href="chat.php" class="text-slate-500 hover:text-accent relative p-2 transition rounded-xl hover:bg-slate-100" title="Messages">
                        <i class="far fa-envelope text-lg"></i>
                        <?php if($unread_count > 0): ?>
                            <span class="absolute top-1 right-1 bg-red-500 text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center border border-white"><?= $unread_count > 9 ? '9+' : $unread_count ?></span>
                        <?php endif; ?>
                    </a>

                    <a href="profile.php" class="flex items-center gap-2 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition">
                        <?php if($header_avatar): ?>
                            <img src="<?= $header_avatar ?>" alt="Profile" class="w-7 h-7 rounded-full object-cover shadow-sm bg-slate-200">
                        <?php else: ?>
                            <div class="w-7 h-7 rounded-full bg-brand/10 text-brand flex items-center justify-center">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                        <?php endif; ?>
                        <span class="text-sm font-semibold text-slate-700 hidden lg:block"><?= htmlspecialchars(explode(' ', trim($_SESSION['user_name']))[0]) ?></span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="font-bold text-slate-600 text-sm sm:text-base hover:text-brand px-3 py-2 rounded-xl hover:bg-slate-100 transition whitespace-nowrap">Login</a>
                <?php endif; ?>

                <a href="post-ad.php" class="flex items-center gap-2 bg-brand text-white font-bold text-sm sm:text-base py-2.5 px-5 rounded-xl hover:bg-brand-light transition shadow-md whitespace-nowrap">
                    <i class="fas fa-plus text-sm"></i> SELL
                </a>
            </div>

            <!-- Mobile right actions -->
            <div class="flex md:hidden items-center gap-1 sm:gap-2 flex-shrink-0">
                <a href="search.php" class="text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 flex-shrink-0">
                    <i class="fas fa-search text-base sm:text-lg"></i>
                </a>
                <a href="post-ad.php" class="bg-brand text-white font-bold text-[10px] sm:text-xs py-1.5 px-2 rounded-lg flex items-center whitespace-nowrap shadow-sm shrink-0">
                    <i class="fas fa-plus mr-1"></i><span class="hidden xs:inline">SELL</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Dropdown -->
    <div id="mobile-menu" class="hidden mt-2 bg-white/90 backdrop-blur-xl border border-white/60 shadow-xl rounded-2xl overflow-hidden">
        <div class="px-4 py-3 space-y-2">
            <form action="search.php" method="GET" class="flex border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                <input type="text" name="q" placeholder="Search..." class="flex-1 px-3 py-2 outline-none text-sm bg-transparent">
                <button type="submit" class="bg-brand text-white px-4"><i class="fas fa-search"></i></button>
            </form>

            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                    <a href="admin/index.php" class="block font-bold text-accent py-2 px-1">Go to Admin Panel</a>
                <?php endif; ?>
                <a href="profile.php" class="flex items-center gap-2 font-medium text-slate-700 py-2 px-1 border-b border-slate-100">
                    <?php if($header_avatar): ?>
                        <img src="<?= $header_avatar ?>" alt="Profile" class="w-6 h-6 rounded-full object-cover">
                    <?php else: ?>
                        <i class="far fa-user-circle text-brand text-lg"></i>
                    <?php endif; ?>
                    <?= htmlspecialchars($_SESSION['user_name']) ?>
                </a>
                <a href="chat.php" class="flex items-center justify-between font-medium text-slate-700 py-2 px-1 border-b border-slate-100">
                    <span><i class="far fa-envelope text-brand mr-2"></i>Messages</span>
                    <?php if($unread_count > 0): ?>
                        <span class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $unread_count ?></span>
                    <?php endif; ?>
                </a>
                <a href="favorites.php" class="flex items-center gap-2 font-medium text-slate-700 py-2 px-1 border-b border-slate-100">
                    <i class="far fa-heart text-red-400"></i> Favorites
                </a>
                <a href="api/auth.php?action=logout" class="block font-medium text-red-600 py-2 px-1">Logout</a>
            <?php else: ?>
                <a href="login.php" class="block font-bold text-brand py-2 px-1">Login / Register</a>
            <?php endif; ?>
            <a href="post-ad.php" class="block bg-brand text-white text-center font-bold rounded-xl py-3">
                <i class="fas fa-plus mr-2"></i> SELL AN ITEM
            </a>
        </div>
    </div>
</header>

<!-- Alerts Layout -->
<div class="app-container mt-28 sm:mt-36">
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
        'invalid_phone' => 'Please enter a valid Pakistan phone number (e.g. 03xx-xxxxxxx).',

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
        'account_verified' => 'Account verified!',

        'email_sent' => 'Reset link sent! Please check your email.'
    ];
    $msg = isset($success_messages[$_GET['success']]) ? $success_messages[$_GET['success']] : 'Success.';
    echo '<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-2 text-sm font-medium"><i class="fas fa-check-circle text-green-500"></i> ' . $msg . '</div>';
}
?>
</div>

<main class="app-container pt-3 sm:pt-10 pb-16 flex-grow">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts
    const alerts = document.querySelectorAll('.bg-red-50, .bg-green-50');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 600);
        }, 4000);
    });

    // Floating header scroll shrink effect
    const header    = document.getElementById('site-header');
    const headerInner = header ? header.querySelector('div') : null;
    if (header && headerInner) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 30) {
                headerInner.classList.add('shadow-[0_4px_24px_rgba(0,0,0,0.15)]', 'bg-white/60');
                headerInner.classList.remove('bg-white/40');
            } else {
                headerInner.classList.remove('shadow-[0_4px_24px_rgba(0,0,0,0.15)]', 'bg-white/60');
                headerInner.classList.add('bg-white/40');
            }
        }, { passive: true });
    }
});
</script>
