<?php
require_once '../includes/config.php';

$stmt = $pdo->query("
    SELECT a.id, a.title, a.category_id, c.name as category_name,
           ai.image_path, ai.is_primary
    FROM ads a
    LEFT JOIN categories c ON c.id = a.category_id
    LEFT JOIN ad_images ai ON ai.ad_id = a.id AND ai.is_primary = 1
    ORDER BY a.category_id, a.id
");
$ads = $stmt->fetchAll();

$noImage = [];
$hasImage = [];
foreach ($ads as $ad) {
    if (empty($ad['image_path'])) {
        $noImage[] = $ad;
    } else {
        $hasImage[] = $ad;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ad Image Checker</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        h1 { color: #333; }
        .stats { background: #fff; border-radius: 8px; padding: 16px; margin-bottom: 20px; display: flex; gap: 20px; }
        .stat { text-align: center; }
        .stat .num { font-size: 2em; font-weight: bold; }
        .stat.ok .num { color: green; }
        .stat.bad .num { color: red; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
        .card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .card img { width: 100%; height: 140px; object-fit: cover; display: block; }
        .card .info { padding: 8px; font-size: 12px; }
        .card .id { color: #999; }
        .card .title { font-weight: bold; margin: 2px 0; color: #333; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card .cat { color: #3a77ff; font-size: 11px; }
        .card .path { color: #aaa; font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .card .broken { background: #fee2e2; color: #dc2626; font-size: 11px; padding: 4px 8px; }
        .no-img { background: #fff3cd; border: 2px dashed #ffc107; }
        .no-img .placeholder { height: 140px; display: flex; align-items: center; justify-content: center; color: #999; font-size: 12px; flex-direction: column; gap: 4px; }
        h2 { margin-top: 30px; }
        .section-bad { background: #fee2e2; border-radius: 8px; padding: 16px; margin-bottom: 10px; }
        .section-good { background: #dcfce7; border-radius: 8px; padding: 16px; }
    </style>
</head>
<body>
    <h1>🔍 Ad Image Checker</h1>

    <div class="stats">
        <div class="stat ok">
            <div class="num"><?= count($hasImage) ?></div>
            <div>Ads with Images</div>
        </div>
        <div class="stat bad">
            <div class="num"><?= count($noImage) ?></div>
            <div>Ads WITHOUT Images</div>
        </div>
        <div class="stat">
            <div class="num"><?= count($ads) ?></div>
            <div>Total Ads</div>
        </div>
    </div>

    <?php if (!empty($noImage)): ?>
    <h2>❌ Ads Missing Images (<?= count($noImage) ?>)</h2>
    <div class="section-bad">
        <div class="grid">
            <?php foreach ($noImage as $ad): ?>
            <div class="card no-img">
                <div class="placeholder">
                    <span>⚠️ No Image</span>
                    <span>Ad #<?= $ad['id'] ?></span>
                </div>
                <div class="info">
                    <div class="id">#<?= $ad['id'] ?></div>
                    <div class="title" title="<?= htmlspecialchars($ad['title']) ?>"><?= htmlspecialchars($ad['title']) ?></div>
                    <div class="cat"><?= htmlspecialchars($ad['category_name']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <h2>✅ Ads with Images (<?= count($hasImage) ?>)</h2>
    <div class="section-good">
        <div class="grid">
            <?php foreach ($hasImage as $ad): 
                $imgUrl = get_ad_image($ad['image_path']);
                // Check if local file exists
                $isLocal = strpos($imgUrl, 'http') !== 0;
                $localPath = __DIR__ . '/../' . $imgUrl;
                $fileExists = $isLocal ? file_exists($localPath) : true;
            ?>
            <div class="card">
                <img src="../<?= htmlspecialchars($imgUrl) ?>" 
                     onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                     alt="<?= htmlspecialchars($ad['title']) ?>">
                <div style="display:none;height:140px;background:#fee2e2;display:none;align-items:center;justify-content:center;color:#dc2626;font-size:11px;" class="broken">⚠️ Image Failed to Load</div>
                <?php if ($isLocal && !$fileExists): ?>
                <div class="broken">⚠️ File not found on disk</div>
                <?php endif; ?>
                <div class="info">
                    <div class="id">#<?= $ad['id'] ?></div>
                    <div class="title" title="<?= htmlspecialchars($ad['title']) ?>"><?= htmlspecialchars($ad['title']) ?></div>
                    <div class="cat"><?= htmlspecialchars($ad['category_name']) ?></div>
                    <div class="path" title="<?= htmlspecialchars($ad['image_path']) ?>"><?= htmlspecialchars($ad['image_path']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
