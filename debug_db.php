<?php
require_once 'includes/config.php';
$stmt = $pdo->query("DESCRIBE ads");
echo "<pre>";
print_r($stmt->fetchAll());
echo "</pre>";
?>
