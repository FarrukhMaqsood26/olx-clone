<?php
require_once 'includes/config.php';

// List of high-quality working Unsplash IDs (verified relevant)
$mobiles = ['1616348436168-de43ad0db179', '1610940886628-2e909c72ba1c', '1591336442651-bb501f375f35', '1580910051074-3eb37ecad6d1', '1598327105666-5b89351aff97', '1523206489230-c012c64b2b48', '1511707171634-5f897ff02aa9', '1551817958-c5b5d3b66c72', '1511707171634-5f897ff02aa9', '1512054502232-10a0a035d672'];
$cars = ['1533473359331-0135ef1b58bf', '1503376780353-7e6692767b70', '1494976388531-d1058494cdd8', '1552519507-da3b142c6e3d', '1555215695-3004980ad54e', '1606152424101-dd29bc7db60e', '1618843479313-40f8afb4b4d8', '1584345604476-8ec5e788fe10', '1609521262047-f8221183184e', '1606664515524-ed2f786a0bd6'];
$property = ['1580587767373-9ed702362f60', '1600585154340-be6161a56a0c', '1613490493576-7fde63acd811', '1512917774080-9991f1c4c750', '1502672260266-1c1ef2d93688', '1500076656116-558758c991c1', '1522708323590-d24dbb6b0267', '1568605114967-8130f3a36994', '1497366216548-37526070297c', '1500382017468-9049fed747ef'];
$electronics = ['1496181133224-d03d26567f21', '1593359677879-a4bb92f829d1', '1606813907291-d86ebb995a26', '1505740420928-5e560c06d30e', '1517336714460-4c9889a1f4c3', '1588872657578-7efd1f1555ed', '1511467687858-23d96c32e4ae', '1544244015-0cd4b3ffc6b0', '1542751371-adc38448a05e', '1498049860635-affd2adba6d3'];

$categories = [
    1 => $mobiles,
    2 => $cars,
    3 => $property,
    4 => $electronics
];

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Clean up database
$pdo->exec("TRUNCATE TABLE ad_images");

$stmt = $pdo->query("SELECT id, category_id, title FROM ads ORDER BY category_id, id");
$ads = $stmt->fetchAll();

$counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

foreach ($ads as $ad) {
    $catId = $ad['category_id'];
    $adId = $ad['id'];
    
    if (!isset($categories[$catId])) continue;
    
    $index = $counts[$catId] % count($categories[$catId]);
    $unsplashId = $categories[$catId][$index];
    $counts[$catId]++;
    
    $url = "https://images.unsplash.com/photo-{$unsplashId}?auto=format&fit=crop&w=800&q=80";
    $filename = "ad_final_{$adId}_" . uniqid() . ".jpg";
    
    echo "Updating Ad $adId ({$ad['title']}) with image $unsplashId...\n";
    
    $content = @file_get_contents($url);
    if ($content) {
        file_put_contents($uploadDir . $filename, $content);
        $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)")
            ->execute([$adId, $uploadDir . $filename]);
        echo "Done.\n";
    } else {
        echo "FAILED to download for Ad $adId\n";
    }
}

echo "Ad image refresh complete.\n";
?>
