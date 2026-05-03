<?php
require_once 'includes/config.php';

$stmt = $pdo->query("
    SELECT a.id, a.title, a.category_id, c.name as category_name,
           ai.image_path
    FROM ads a
    LEFT JOIN categories c ON c.id = a.category_id
    LEFT JOIN ad_images ai ON ai.ad_id = a.id AND ai.is_primary = 1
    ORDER BY a.category_id, a.id
");
$ads = $stmt->fetchAll();

$noImage     = [];
$fileNotFound = [];
$ok          = [];

foreach ($ads as $ad) {
    if (empty($ad['image_path'])) {
        $noImage[] = $ad;
        continue;
    }
    $imgPath = $ad['image_path'];
    if (strpos($imgPath, 'http') !== 0) {
        $localFile = 'c:/xampp/htdocs/bazaar/' . ltrim($imgPath, '/');
        if (!file_exists($localFile)) {
            $ad['resolved'] = $localFile;
            $fileNotFound[] = $ad;
        } else {
            $ok[] = $ad;
        }
    } else {
        $ok[] = $ad;
    }
}

echo "=== AD IMAGE REPORT ===\n";
echo "Total ads: " . count($ads) . "\n\n";

echo "--- MISSING IMAGE IN DB (" . count($noImage) . ") ---\n";
foreach ($noImage as $a) {
    echo "  Ad #{$a['id']}: {$a['title']} [{$a['category_name']}]\n";
}

echo "\n--- FILE NOT FOUND ON DISK (" . count($fileNotFound) . ") ---\n";
foreach ($fileNotFound as $a) {
    echo "  Ad #{$a['id']}: {$a['title']} | DB: {$a['image_path']}\n";
}

echo "\n--- OK (" . count($ok) . ") ---\n";
foreach ($ok as $a) {
    echo "  Ad #{$a['id']}: {$a['title']} [{$a['category_name']}] => {$a['image_path']}\n";
}
?>
