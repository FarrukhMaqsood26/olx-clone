<?php
require_once 'includes/config.php';
$fixMap = [
    25 => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800',
    38 => 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=800',
    40 => 'https://images.unsplash.com/photo-1552282324-429383685e97?w=800'
];
$uploadDir = 'uploads/';
foreach ($fixMap as $adId => $source) {
    $filename = "ad_{$adId}_fixed_" . time() . ".jpg";
    $content = file_get_contents($source);
    if ($content) {
        file_put_contents($uploadDir . $filename, $content);
        $pdo->prepare("DELETE FROM ad_images WHERE ad_id = ?")->execute([$adId]);
        $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)")
            ->execute([$adId, $uploadDir . $filename]);
    }
}
echo "Fixed 3 ads.\n";
?>
