<?php
require_once '../includes/config.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `password_resets` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `email` varchar(100) NOT NULL,
      `token` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "password_resets table created successfully.";
} catch(PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
