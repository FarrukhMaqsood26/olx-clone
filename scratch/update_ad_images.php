<?php
require_once 'includes/config.php';

// Mapping of AD ID to Image Source (either local brain path or Unsplash ID)
$imageMap = [
    1 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/iphone_15_pro_ad_1777724545499.png',
    2 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/samsung_s24_ultra_ad_1777724698700.png',
    3 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/google_pixel_8_ad_1777725005732.png',
    4 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/oneplus_12_ad_1777725044011.png',
    5 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/xiaomi_14_ad_1777725283543.png',
    6 => 'C:/Users/Farrukh/.gemini/antigravity/brain/6722c348-5f6e-4b75-b380-0d7ddc78e866/nothing_phone_2_ad_1777725318363.png',
    
    // Unsplash Mobiles
    7 => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?w=800',
    8 => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=800',
    9 => 'https://images.unsplash.com/photo-1523206489230-c012c64b2b48?w=800',
    10 => 'https://images.unsplash.com/photo-1551817958-c5b5d3b66c72?w=800',
    
    // Vehicles
    11 => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800',
    12 => 'https://images.unsplash.com/photo-1594070319944-7c0c6bb88825?w=800',
    13 => 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2?w=800',
    14 => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?w=800',
    15 => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800',
    16 => 'https://images.unsplash.com/photo-1606152424101-dd29bc7db60e?w=800',
    17 => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800',
    18 => 'https://images.unsplash.com/photo-1584345604476-8ec5e788fe10?w=800',
    19 => 'https://images.unsplash.com/photo-1609521262047-f8221183184e?w=800',
    20 => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800',
    
    // Property
    21 => 'https://images.unsplash.com/photo-1580587767373-9ed702362f60?w=800',
    22 => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=800',
    23 => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800',
    24 => 'https://images.unsplash.com/photo-1500076656116-558758c991c1?w=800',
    25 => 'https://images.unsplash.com/photo-1555436169-20d9321f98c5?w=800',
    26 => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800',
    27 => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?w=800',
    28 => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800',
    29 => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800',
    30 => 'https://images.unsplash.com/photo-1524813686514-a57563d77965?w=800',
    
    // Electronics
    31 => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800',
    32 => 'https://images.unsplash.com/photo-1517336714460-4c9889a1f4c3?w=800',
    33 => 'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?w=800',
    34 => 'https://images.unsplash.com/photo-1606813907291-d86ebb995a26?w=800',
    35 => 'https://images.unsplash.com/photo-1605901309584-818e25960a8f?w=800',
    36 => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800',
    37 => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=800',
    38 => 'https://images.unsplash.com/photo-1544244015-0cd4b3ffc6b0?w=800',
    39 => 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=800',
    40 => 'https://images.unsplash.com/photo-1461151304267-38535e770f76?w=800'
];

$uploadDir = 'uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

foreach ($imageMap as $adId => $source) {
    echo "Processing Ad $adId...\n";
    
    $filename = "ad_{$adId}_" . time() . ".jpg";
    if (strpos($source, 'http') === 0) {
        // Download from Unsplash
        $content = file_get_contents($source);
        if ($content !== false) {
            file_put_contents($uploadDir . $filename, $content);
        } else {
            echo "Failed to download $source\n";
            continue;
        }
    } else {
        // Copy from local brain
        if (file_exists($source)) {
            copy($source, $uploadDir . $filename);
        } else {
            echo "Local file not found: $source\n";
            continue;
        }
    }
    
    // Update DB: Clear existing images and insert new one as primary
    $pdo->prepare("DELETE FROM ad_images WHERE ad_id = ?")->execute([$adId]);
    $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)")
        ->execute([$adId, $uploadDir . $filename]);
        
    echo "Successfully updated Ad $adId with image $filename\n";
}

echo "All 40 ads updated successfully.\n";
?>
