<?php
require_once '../includes/config.php';

try {
    // 1. Create messages table if it doesn't exist
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `messages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sender_id` int(11) NOT NULL,
            `receiver_id` int(11) NOT NULL,
            `ad_id` int(11) DEFAULT NULL,
            `content` text DEFAULT NULL,
            `is_read` tinyint(1) DEFAULT 0,
            `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
            PRIMARY KEY (`id`),
            FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
            FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
            FOREIGN KEY (`ad_id`) REFERENCES `ads` (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Messages table checked/created.<br>";

    // 2. Add file_path column
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN file_path VARCHAR(255) NULL;");
        echo "Column 'file_path' added.<br>";
    } catch(PDOException $e) {
        echo "Column 'file_path' might already exist or error: " . $e->getMessage() . "<br>";
    }

    // 3. Add file_type column
    try {
        $pdo->exec("ALTER TABLE messages ADD COLUMN file_type ENUM('text', 'image', 'audio') DEFAULT 'text';");
        echo "Column 'file_type' added.<br>";
    } catch(PDOException $e) {
        echo "Column 'file_type' might already exist or error: " . $e->getMessage() . "<br>";
    }

    echo "<strong>Upgrade successful!</strong>";
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>
