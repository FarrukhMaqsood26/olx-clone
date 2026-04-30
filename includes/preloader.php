<!-- ===== SITE PRELOADER ===== -->
<div id="sitePreloader" class="fixed inset-0 bg-white z-[9999] flex flex-col items-center justify-center transition-all duration-700 ease-in-out">
    <div class="relative flex flex-col items-center">
        <!-- Logo Text -->
        <div class="mb-8 opacity-0 animate-[fadeIn_0.5s_ease-out_forwards]">
            <span class="text-4xl font-extrabold text-brand tracking-tighter">OLX</span>
            <span class="text-xs font-bold text-white bg-accent px-2 py-1 rounded-md ml-1">PK</span>
        </div>
        
        <!-- Premium Loader -->
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-accent border-t-transparent rounded-full animate-spin"></div>
        </div>
        
        <!-- Subtle Text -->
        <p class="mt-6 text-slate-400 text-[10px] font-bold uppercase tracking-[0.2em] animate-pulse">Loading Excellence</p>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .preloader-hidden {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
</style>

<script>
    window.addEventListener('load', function() {
        const loader = document.getElementById('sitePreloader');
        if (loader) {
            setTimeout(() => {
                loader.classList.add('preloader-hidden');
                setTimeout(() => loader.remove(), 700);
            }, 600);
        }
    });
</script>
