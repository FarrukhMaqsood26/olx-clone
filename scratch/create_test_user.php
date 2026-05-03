<?php
require_once __DIR__ . '/../includes/config.php';

$name = 'Test User';
$email = 'test@example.com';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_BCRYPT);
$phone = '1234567890';

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Update password
        $stmt = $pdo->prepare("UPDATE users SET password = ?, is_email_verified = 1 WHERE email = ?");
        $stmt->execute([$hashed_password, $email]);
        echo "Updated existing test user: $email\n";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, phone, is_email_verified) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$name, $email, $hashed_password, $phone]);
        echo "Created new test user: $email\n";
    }
    echo "Password: $password\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
