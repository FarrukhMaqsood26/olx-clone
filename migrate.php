<?php
require_once __DIR__ . '/includes/config.php';

try {
    $pdo->exec("ALTER TABLE users CHANGE is_phone_verified is_email_verified TINYINT(1) DEFAULT 0");
    $pdo->exec("ALTER TABLE users CHANGE phone_otp email_verification_token VARCHAR(100) DEFAULT NULL");
    
    // Also update schema.sql locally so it stays in sync
    $schemaFile = __DIR__ . '/database/schema.sql';
    if(file_exists($schemaFile)) {
        $content = file_get_contents($schemaFile);
        $content = str_replace("is_phone_verified BOOLEAN DEFAULT FALSE", "is_email_verified BOOLEAN DEFAULT FALSE", $content);
        $content = str_replace("phone_otp VARCHAR(10)", "email_verification_token VARCHAR(100)", $content);
        file_put_contents($schemaFile, $content);
    }
    
    echo "Database migration successful.";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}
