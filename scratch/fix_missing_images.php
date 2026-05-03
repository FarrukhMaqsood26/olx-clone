<?php
require_once 'includes/config.php';

// Ads missing images:
// #2  Samsung S24 Ultra  [Mobiles]
// #3  Google Pixel 8     [Mobiles]
// #4  OnePlus 12         [Mobiles]
// #8  Asus ROG Phone 8   [Mobiles]

$missing = [
    2 => [
        'url'   => 'https://images.unsplash.com/photo-1610945264803-c22b62831ba4?w=800&q=80',
        'title' => 'Samsung S24 Ultra',
    ],
    3 => [
        'url'   => 'https://images.unsplash.com/photo-1666881335584-7ad5d04a598b?w=800&q=80',
        'title' => 'Google Pixel 8',
    ],
    4 => [
        'url'   => 'https://images.unsplash.com/photo-1658840220428-0a63a1e37e25?w=800&q=80',
        'title' => 'OnePlus 12',
    ],
    8 => [
        'url'   => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800&q=80',
        'title' => 'Asus ROG Phone 8',
    ],
];

$uploadDir = 'uploads/';

foreach ($missing as $adId => $info) {
    echo "Processing Ad #{$adId}: {$info['title']}...\n";

    $filename = "ad_fix_{$adId}_" . time() . ".jpg";
    $dest     = $uploadDir . $filename;

    $content = @file_get_contents($info['url']);
    if ($content === false) {
        echo "  FAILED to download image. Trying fallback...\n";
        // Fallback: use a generic smartphone image
        $fallback = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800&q=80';
        $content  = @file_get_contents($fallback);
    }

    if ($content) {
        file_put_contents($dest, $content);
        // Remove any old entry first, then insert
        $pdo->prepare("DELETE FROM ad_images WHERE ad_id = ?")->execute([$adId]);
        $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)")
            ->execute([$adId, $dest]);
        echo "  ✓ Saved as {$filename} and updated DB.\n";
    } else {
        echo "  ✗ FAILED completely for Ad #{$adId}.\n";
    }
}

echo "\nDone! Re-run img_check_cli.php to verify.\n";
?>
