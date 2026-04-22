    <footer class="glass-panel">
        <div>
            <h4>POPULAR CATEGORIES</h4>
            <ul>
                <li><a href="search.php?category=vehicles">Cars</a></li>
                <li><a href="search.php?category=property">Flats for rent</a></li>
                <li><a href="search.php?category=mobiles">Mobile Phones</a></li>
                <li><a href="search.php?category=electronics">Electronics</a></li>
            </ul>
        </div>
        <div>
            <h4>TRENDING SEARCHES</h4>
            <ul>
                <li><a href="search.php?q=bikes">Bikes</a></li>
                <li><a href="search.php?q=watches">Watches</a></li>
                <li><a href="search.php?q=laptops">Laptops</a></li>
                <li><a href="search.php?q=iphone">iPhone</a></li>
            </ul>
        </div>
        <div>
            <h4>ABOUT US</h4>
            <ul>
                <li><a href="#">About OLX Group</a></li>
                <li><a href="#">OLX Blog</a></li>
                <li><a href="#">Contact Us</a></li>
                <li><a href="#">OLX for Businesses</a></li>
            </ul>
        </div>
        <div>
            <h4>OLX</h4>
            <ul>
                <li><a href="#">Help</a></li>
                <li><a href="#">Sitemap</a></li>
                <li><a href="#">Terms of use</a></li>
                <li><a href="#">Privacy Policy</a></li>
            </ul>
            <div style="margin-top:20px; display:flex; gap:12px;">
                <a href="#" style="width:36px; height:36px; border-radius:50%; background:rgba(0,47,52,0.08); display:flex; align-items:center; justify-content:center; color:var(--primary-teal); transition:all 0.2s;" onmouseover="this.style.background='var(--primary-teal)'; this.style.color='white';" onmouseout="this.style.background='rgba(0,47,52,0.08)'; this.style.color='var(--primary-teal)';"><i class="fab fa-facebook-f"></i></a>
                <a href="#" style="width:36px; height:36px; border-radius:50%; background:rgba(0,47,52,0.08); display:flex; align-items:center; justify-content:center; color:var(--primary-teal); transition:all 0.2s;" onmouseover="this.style.background='var(--primary-teal)'; this.style.color='white';" onmouseout="this.style.background='rgba(0,47,52,0.08)'; this.style.color='var(--primary-teal)';"><i class="fab fa-twitter"></i></a>
                <a href="#" style="width:36px; height:36px; border-radius:50%; background:rgba(0,47,52,0.08); display:flex; align-items:center; justify-content:center; color:var(--primary-teal); transition:all 0.2s;" onmouseover="this.style.background='var(--primary-teal)'; this.style.color='white';" onmouseout="this.style.background='rgba(0,47,52,0.08)'; this.style.color='var(--primary-teal)';"><i class="fab fa-instagram"></i></a>
            </div>
        </div>
    </footer>
    <div style="text-align:center; padding:15px; font-size:12px; color:var(--text-secondary); background:rgba(255,255,255,0.3);">
        &copy; <?= date('Y') ?> OLX Clone &mdash; Database Project. All rights reserved.
    </div>
</body>
</html>
