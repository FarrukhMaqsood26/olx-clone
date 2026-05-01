</main>

<footer class="bg-white border-t border-slate-200 mt-auto">
    <div class="app-container py-10 md:py-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-slate-900 font-bold text-sm uppercase tracking-wide mb-4">Popular Categories</h4>
                <ul class="space-y-3">
                    <li><a href="search.php?category=1"
                            class="text-sm text-slate-500 hover:text-accent transition">Cars</a></li>
                    <li><a href="search.php?category=2"
                            class="text-sm text-slate-500 hover:text-accent transition">Flats for rent</a></li>
                    <li><a href="search.php?category=3"
                            class="text-sm text-slate-500 hover:text-accent transition">Mobile Phones</a></li>
                    <li><a href="search.php?category=4"
                            class="text-sm text-slate-500 hover:text-accent transition">Jobs</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-slate-900 font-bold text-sm uppercase tracking-wide mb-4">Trending Searches</h4>
                <ul class="space-y-3">
                    <li><a href="search.php?q=Bikes"
                            class="text-sm text-slate-500 hover:text-accent transition">Bikes</a></li>
                    <li><a href="search.php?q=Watches"
                            class="text-sm text-slate-500 hover:text-accent transition">Watches</a></li>
                    <li><a href="search.php?q=Books"
                            class="text-sm text-slate-500 hover:text-accent transition">Books</a></li>
                    <li><a href="search.php?q=Dogs" class="text-sm text-slate-500 hover:text-accent transition">Dogs</a>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="text-slate-900 font-bold text-sm uppercase tracking-wide mb-4">About Us</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">About Bazaar Group</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Bazaar Blog</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Contact Us</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Bazaar for Businesses</a>
                    </li>
                </ul>
            </div>

            <div>
                <h4 class="text-slate-900 font-bold text-sm uppercase tracking-wide mb-4">Bazaar</h4>
                <ul class="space-y-3">
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Help</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Sitemap</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Terms of use</a></li>
                    <li><a href="#" class="text-sm text-slate-500 hover:text-accent transition">Privacy Policy</a></li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-8 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4">
                <p class="text-xs font-bold text-brand uppercase tracking-wider">Follow Us</p>
                <div class="flex gap-3">
                    <a href="#" class="text-slate-400 hover:text-brand transition"><i
                            class="fab fa-facebook-f text-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-brand transition"><i
                            class="fab fa-twitter text-lg"></i></a>
                    <a href="#" class="text-slate-400 hover:text-brand transition"><i
                            class="fab fa-instagram text-lg"></i></a>
                </div>
            </div>
            <p class="text-xs text-slate-500">Free Classifieds in Pakistan . &copy; 2006-2026 Bazaar</p>
        </div>
    </div>
</footer>

<script>
    // Toast notification handling
    function showToast(message, type = 'success') {
        const bg = type === 'success' ? 'bg-green-500' : 'bg-red-500';
        const toast = document.createElement('div');
        toast.className = `fixed bottom-4 right-4 ${bg} text-white px-6 py-3 rounded shadow-lg transform transition-all duration-300 translate-y-0 opacity-100 z-50`;
        toast.innerText = message;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('translate-y-2', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 1000);
    }

    // Global Favorite Toggle
    function toggleFavorite(adId, btn) {
        event.preventDefault();
        event.stopPropagation();

        $.ajax({
            url: 'api/favorites.php',
            type: 'POST',
            data: { ad_id: adId },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    const icon = $(btn).find('i');
                    if (response.action === 'added') {
                        icon.removeClass('far text-slate-400').addClass('fas text-red-500');
                        showToast('Added to favorites!');
                    } else {
                        icon.removeClass('fas text-red-500').addClass('far text-slate-400');
                        showToast('Removed from favorites!');
                    }
                } else if (response.message === 'login_required') {
                    showToast('Please login to add favorites', 'error');
                    setTimeout(() => window.location.href = 'login.php', 1500);
                } else {
                    showToast(response.message || 'Error updating favorites', 'error');
                }
            },
            error: function () {
                showToast('Something went wrong', 'error');
            }
        });
    }
</script>
</body>

</html>