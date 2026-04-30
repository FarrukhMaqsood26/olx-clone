<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__.'/check_admin.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OLX Admin Panel</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tailwind CSS (Play CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            DEFAULT: '#0f172a', /* deep slate for admin */
                            light: '#1e293b',
                        },
                        accent: {
                            DEFAULT: '#3b82f6',
                            hover: '#2563eb'
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
    <style>
        /* Mobile sidebar hidden by default, visible when active */
        #adminSidebar.active { transform: translateX(0); }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased flex h-screen overflow-hidden">

    <!-- Mobile overlay -->
    <div id="sidebarOverlay" class="fixed inset-0 bg-slate-900/50 z-40 hidden md:hidden" onclick="toggleAdminSidebar()"></div>

    <!-- Sidebar -->
    <aside id="adminSidebar" class="bg-brand w-64 fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 md:relative flex flex-col transition-transform duration-300 shadow-xl">
        <div class="h-16 flex items-center justify-between px-6 bg-slate-900 border-b border-white/10">
            <a href="index.php" class="text-white font-extrabold tracking-widest text-xl">OLX<span class="text-accent font-light">ADMIN</span></a>
            <button class="text-slate-400 hover:text-white md:hidden" onclick="toggleAdminSidebar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 border-b border-white/5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center text-xl shrink-0">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="overflow-hidden">
                <p class="text-white font-bold text-sm truncate"><?= htmlspecialchars($_SESSION['user_name']) ?></p>
                <p class="text-emerald-400 text-xs font-semibold uppercase tracking-wider mt-0.5">Admin</p>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
            <a href="index.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page == 'index.php' ? 'bg-accent text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-tachometer-alt w-5 text-center"></i> Dashboard
            </a>
            <a href="ads.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page == 'ads.php' ? 'bg-accent text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-bullhorn w-5 text-center"></i> Manage Ads
            </a>
            <a href="categories.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page == 'categories.php' ? 'bg-accent text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-tags w-5 text-center"></i> Categories
            </a>
            <a href="users.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold transition <?= $current_page == 'users.php' ? 'bg-accent text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
                <i class="fas fa-users w-5 text-center"></i> Users
            </a>

            
            <div class="pt-6 mt-6 border-t border-white/5 space-y-1">
                <a href="../index.php" target="_blank" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-slate-400 hover:bg-slate-800 hover:text-white transition">
                    <i class="fas fa-external-link-alt w-5 text-center"></i> View Live Site
                </a>
                <a href="../api/auth.php?action=logout" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-semibold text-red-400 hover:bg-slate-800 hover:text-red-300 transition mt-auto">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i> Log Out
                </a>
            </div>
        </nav>
    </aside>

    <!-- Main ContentWrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 relative">
        <!-- Top Navbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 z-30 shadow-sm relative">
            <div class="flex items-center gap-4">
                <button class="md:hidden text-slate-500 hover:text-slate-800 focus:outline-none" onclick="toggleAdminSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <h1 class="font-bold text-lg text-slate-800 hidden sm:block">Control Panel</h1>
            </div>
        </header>

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-4 sm:p-6 lg:p-8">
            <!-- Alerts -->
            <?php if(isset($_GET['success'])): ?>
                <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 p-4 rounded shadow-sm flex items-start">
                    <i class="fas fa-check-circle text-emerald-500 mt-0.5 mr-3"></i>
                    <p class="font-medium text-sm">Action completed successfully.</p>
                </div>
            <?php endif; ?>
            <?php if(isset($_GET['error'])): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-800 p-4 rounded shadow-sm flex items-start">
                    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3"></i>
                    <p class="font-medium text-sm"><?= htmlspecialchars($_GET['error']) ?></p>
                </div>
            <?php endif; ?>
