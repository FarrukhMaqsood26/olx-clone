<?php
// api/auth.php
require_once '../includes/config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';


// 1. STANDARD LOGIN

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $identifier = sanitize_input($_POST['identifier']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_email_verified FROM users WHERE email = ? OR name = ? OR phone = ?");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_email_verified'] == 0) {
            $token = bin2hex(random_bytes(32));
            $upd = $pdo->prepare("UPDATE users SET email_verification_token = ? WHERE id = ?");
            $upd->execute([$token, $user['id']]);
            header("Location: ../verify-email.php?email=" . urlencode($user['email']) . "&simulated_token=" . $token);
            exit;
        }
        
        // Password is correct & verified, start session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];


        // REMEMBER ME Logic

        if (isset($_POST['remember_me'])) {
            $selector = bin2hex(random_bytes(8));
            $token = bin2hex(random_bytes(32));
            $hashedToken = password_hash($token, PASSWORD_DEFAULT);
            $expiry = date('Y-m-d H:i:s', time() + 86400 * 30); // 30 days

            // Clean up old tokens for this user
            $pdo->prepare("DELETE FROM user_tokens WHERE user_id = ?")->execute([$user['id']]);

            // Save new token
            $stmt = $pdo->prepare("INSERT INTO user_tokens (user_id, selector, token, expires) VALUES (?, ?, ?, ?)");
            $stmt->execute([$user['id'], $selector, $hashedToken, $expiry]);

            // Set cookie: selector:token
            setcookie(
                'remember_me',
                $selector . ':' . $token,
                time() + 86400 * 30,
                '/',
                '',
                false, // Set to true if using HTTPS
                true   // HttpOnly
            );
        }

        header("Location: ../index.php");
        exit;
    } else {
        // Invalid credentials
        header("Location: ../login.php?error=invalid_credentials");
        exit;
    }
}


// 2. STANDARD SIGNUP

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email or phone exists
    $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
    $check_stmt->execute([$email, $phone]);
    
    if ($check_stmt->rowCount() > 0) {
        header("Location: ../signup.php?error=email_exists");
        exit;
    } else {
        $token = bin2hex(random_bytes(32));
        $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, is_email_verified, email_verification_token) VALUES (?, ?, ?, ?, 0, ?)");
        if ($insert_stmt->execute([$name, $email, $phone, $password, $token])) {
            header("Location: ../verify-email.php?email=" . urlencode($email) . "&simulated_token=" . $token);
            exit;
        } else {
            header("Location: ../signup.php?error=registration_failed");
            exit;
        }
    }
}


// 3. GOOGLE LOGIN INITIATION

if ($action == 'google_login') {
    // Generate state for CSRF protection
    $_SESSION['google_state'] = bin2hex(random_bytes(16));
    
    // Redirect to Google's OAuth 2.0 server
    $google_oauth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URL,
        'response_type' => 'code',
        'scope' => 'email profile',
        'state' => $_SESSION['google_state']
    ]);
    
    header("Location: " . $google_oauth_url);
    exit;
}


// 4. GOOGLE CALLBACK LOGIC

if ($action == 'google_callback') {
    // Validate state
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['google_state']) {
        die('Invalid state parameter.');
    }

    if (isset($_GET['code'])) {
        // Exchange code for access token
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost XAMPP compatibility
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URL,
            'grant_type' => 'authorization_code',
            'code' => $_GET['code']
        ]));
        $response = curl_exec($ch);
        curl_close($ch);
        
        $token_data = json_decode($response, true);
        if (isset($token_data['error'])) {
            die('Google OAuth Error: ' . $token_data['error_description']);
        }
        
        $access_token = $token_data['access_token'];

        // Get user profile info
        $ch = curl_init("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $access_token);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For localhost XAMPP compatibility
        $profile_resp = curl_exec($ch);
        curl_close($ch);
        
        $profile_data = json_decode($profile_resp, true);
        
        if (isset($profile_data['email'])) {
            $google_user_email = $profile_data['email'];
            $google_user_name = isset($profile_data['name']) ? $profile_data['name'] : 'Google User';
        } else {
            die('Could not retrieve email from Google.');
        }
        
        // 2. Check if user exists in our DB
        $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
        $stmt->execute([$google_user_email]);
        $user = $stmt->fetch();
        
        if ($user) {
            // User exists, log them in
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
        } else {
            // User does not exist, create new account
            // Random secure password since they use Google
            $random_password = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
            $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $insert_stmt->execute([$google_user_name, $google_user_email, $random_password]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['user_name'] = $google_user_name;
            $_SESSION['user_role'] = 'user';
        }
        
        header("Location: ../index.php");
        exit;
    }
}


// 5. UPDATE PROFILE

if ($action == 'update_profile' && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php?error=login_required");
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    $name = sanitize_input($_POST['name']);
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : null;
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    
    try {
        if (!empty($new_password)) {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $hashed, $user_id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $user_id]);
        }
        
        $_SESSION['user_name'] = $name;
        header("Location: ../profile.php?success=profile_updated");
    } catch(PDOException $e) {
        header("Location: ../profile.php?error=update_failed");
    }
    exit;
}


// 6. FORGOT & RESET PASSWORD

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'forgot_password') {
    $email = sanitize_input($_POST['email']);
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $token = bin2hex(random_bytes(16));
        $insert = $pdo->prepare("INSERT INTO password_resets (email, token) VALUES (?, ?)");
        $insert->execute([$email, $token]);
        
        // Return simulated email link via flash message for testing
        $reset_link = "reset-password.php?token=" . $token;
        header("Location: ../login.php?success=reset_link_sent&link=" . urlencode($reset_link));
        exit;
    } else {
        header("Location: ../forgot-password.php?error=email_not_found");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'reset_password') {
    $token = $_POST['token'];
    $new_password = $_POST['new_password'];
    
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if ($reset) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update->execute([$hashed, $reset['email']]);
        
        $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
        $del->execute([$reset['email']]);
        
        header("Location: ../login.php?success=password_reset");
        exit;
    } else {
        header("Location: ../login.php?error=invalid_token");
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && $action == 'verify_email') {
    $token = isset($_GET['token']) ? sanitize_input($_GET['token']) : '';
    
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email_verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $upd = $pdo->prepare("UPDATE users SET is_email_verified = 1, email_verification_token = NULL WHERE id = ?");
        $upd->execute([$user['id']]);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: ../index.php?success=email_verified");
        exit;
    } else {
        header("Location: ../login.php?error=invalid_verification_token");
        exit;
    }
}


// 7. LOGOUT

if ($action == 'logout') {
    // Clear Remember Me cookie
    if (isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) === 2) {
            $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?")->execute([$parts[0]]);
        }
        setcookie('remember_me', '', time() - 3600, '/');
    }

    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}
?>
