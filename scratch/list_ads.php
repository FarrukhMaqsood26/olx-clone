<?php
require_once 'includes/config.php';
$stmt = $pdo->query("SELECT id, category_id, title FROM ads ORDER BY category_id, id");
$ads = $stmt->fetchAll();
foreach ($ads as $ad) {
    echo "ID: {$ad['id']} | CAT: {$ad['category_id']} | TITLE: {$ad['title']}\n";
}
?>
