<?php
require_once 'includes/config.php';
try {
    $pdo->exec("ALTER TABLE ads MODIFY COLUMN location VARCHAR(255) NOT NULL");
    echo "SUCCESS: Location column increased to 255 characters.";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
