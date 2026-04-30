<a href="ad.php?id=<?= $ad['id'] ?>" class="group bg-white border border-slate-200 rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col relative block">
    <?php $isFav = (isset($ad['is_favorited']) && $ad['is_favorited'] > 0); ?>
    <button onclick="toggleFavorite(<?= $ad['id'] ?>, this)" class="absolute top-3 right-3 bg-white/90 backdrop-blur text-slate-400 w-9 h-9 rounded-full flex items-center justify-center shadow-sm z-10 transition hover:bg-white group/fav">
        <i class="<?= $isFav ? 'fas text-red-500' : 'far text-slate-400 group-hover/fav:text-red-500' ?> fa-heart text-lg transition-colors"></i>
    </button>
    
    <?php 
        $img = get_ad_image($ad['main_image']); 
    ?>
    <div class="aspect-[4/3] overflow-hidden border-b border-slate-100 bg-slate-50 flex items-center justify-center">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($ad['title']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700" loading="lazy">
    </div>
    
    <div class="p-4 flex flex-col flex-1">
        <div class="text-xl font-extrabold text-slate-900 mb-1">Rs <?= number_format($ad['price']) ?></div>
        <div class="text-sm text-slate-600 line-clamp-2 mb-4 flex-1 leading-snug font-medium group-hover:text-blue-600 transition-colors"><?= htmlspecialchars($ad['title']) ?></div>
        
        <div class="flex items-center justify-between mt-auto">
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5 truncate max-w-[70%]">
                <i class="fas fa-map-marker-alt text-blue-500"></i> <?= htmlspecialchars($ad['location']) ?>
            </div>
            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                <?= date('M d', strtotime($ad['created_at'])) ?>
            </div>
        </div>
    </div>
</a>
