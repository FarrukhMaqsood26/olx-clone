<?php
require_once 'includes/config.php';
$failures = [
    16 => 'audi', 18 => 'mustang', 19 => 'sportage', 21 => 'house',
    31 => 'tv', 33 => 'laptop', 35 => 'xbox', 38 => 'ipad', 40 => 'oled', 41 => 'product'
];
$uploadDir = 'uploads/';
foreach ($failures as $adId => $kw) {
    $url = "https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800"; // Backup working ID
    // Try to get a specific one based on keyword
    $filename = "ad_fix_{$adId}_" . time() . ".jpg";
    $content = @file_get_contents("https://source.unsplash.com/800x600/?$kw"); // source.unsplash still works for redirects
    if (!$content) $content = @file_get_contents($url);
    if ($content) {
        file_put_contents($uploadDir . $filename, $content);
        $pdo->prepare("DELETE FROM ad_images WHERE ad_id = ?")->execute([$adId]);
        $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)")
            ->execute([$adId, $uploadDir . $filename]);
    }
}
echo "Gaps filled.\n";
?>
