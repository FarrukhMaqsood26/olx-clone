<?php
require_once __DIR__ . '/../includes/config.php';

$user_id = 1;
$categories = [
    1 => ['Mobiles', ['iPhone 15 Pro', 'Samsung S24 Ultra', 'Google Pixel 8', 'OnePlus 12', 'Xiaomi 14', 'Nothing Phone 2', 'Sony Xperia 1 V', 'Asus ROG Phone 8', 'Motorola Edge 50', 'Huawei P60 Pro']],
    2 => ['Vehicles', ['Toyota Corolla 2024', 'Honda Civic Reborn', 'Suzuki Swift', 'Tesla Model 3', 'BMW 3 Series', 'Audi A4', 'Mercedes C-Class', 'Ford Mustang', 'Kia Sportage', 'Hyundai Tucson']],
    3 => ['Property', ['5 Marla House in DHA', '10 Marla Plot in Bahria', 'Luxury Apartment in Gulberg', 'Farmhouse near Lahore', 'Commercial Shop in Liberty', 'Studio Flat in Islamabad', 'Modern Villa in Karachi', 'Penthouse with Sea View', 'Office Space in Blue Area', 'Residential Plot in Gwadar']],
    4 => ['Electronics', ['Sony 65" 4K TV', 'MacBook Pro M3', 'Dell XPS 15', 'PlayStation 5 Slim', 'Xbox Series X', 'Bose QuietComfort Ultra', 'Canon EOS R5', 'iPad Pro 12.9', 'Logitech G Pro Keyboard', 'LG OLED C3']]
];

try {
    // Clear existing ads to avoid duplicates during testing
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE ads; TRUNCATE TABLE ad_images; SET FOREIGN_KEY_CHECKS = 1;");

    $pdo->beginTransaction();

    foreach ($categories as $cat_id => $data) {
        $cat_name = $data[0];
        $items = $data[1];
        
        foreach ($items as $index => $title) {
            $price = rand(100, 500000);
            $desc = "This is a premium " . $title . " in excellent condition. Perfect for buyers looking for quality and reliability in the " . $cat_name . " category. Contact for more details.";
            $location = ['Lahore', 'Karachi', 'Islamabad', 'Faisalabad', 'Multan'][rand(0, 4)];
            $condition = ['new', 'used'][rand(0, 1)];
            
            $stmt = $pdo->prepare("INSERT INTO ads (user_id, category_id, title, description, price, condition_type, location, status) 
                                   VALUES (:u, :c, :t, :d, :p, :ct, :l, 'active')");
            $stmt->execute([
                'u' => $user_id,
                'c' => $cat_id,
                't' => $title,
                'd' => $desc,
                'p' => $price,
                'ct' => $condition,
                'l' => $location
            ]);
            
            $ad_id = $pdo->lastInsertId();
            
            // Add a placeholder image
            $stmt = $pdo->prepare("INSERT INTO ad_images (ad_id, image_path, is_primary) VALUES (?, ?, 1)");
            $stmt->execute([$ad_id, 'https://picsum.photos/seed/' . urlencode($title) . '/800/600']);
        }
    }
    
    $pdo->commit();
    echo "Successfully added 40 ads (10 per category).\n";
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
