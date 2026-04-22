<?php
require_once '../includes/config.php';
try {
    $pdo->exec("ALTER TABLE users 
                ADD COLUMN is_phone_verified TINYINT(1) DEFAULT 0,
                ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL;");
    echo "users table altered successfully.";
} catch(PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Columns already exist.";
    } else {
        echo "ERROR: " . $e->getMessage();
    }
}
?>
