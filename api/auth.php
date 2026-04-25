<?php
// api/auth.php
require_once '../includes/config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * Utility to send email, with fallback to simulation
 */
function send_auth_email($to, $subject, $message) {
    $headers = "From: no-reply@olx-clone.local\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    try {
        @mail($to, $subject, $message, $headers);
    } catch (Exception $e) {}
}

// ---------------------------------------------------------
// 1. SIGNUP (Phase 1: Details Submission)
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = sanitize_input($_POST['name']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check uniqueness
    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? OR phone = ?");
    $check->execute([$username, $email, $phone]);
    
    if ($check->rowCount() > 0) {
        header("Location: ../signup.php?error=already_exists");
        exit;
    }

    // Generate 6-digit OTP
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, is_email_verified, verification_code) VALUES (?, ?, ?, ?, ?, 0, ?)");
    if ($stmt->execute([$name, $username, $email, $phone, $password, $otp])) {
        // Send OTP
        $msg = "Your OLX verification code is: <strong>$otp</strong>";
        send_auth_email($email, "Verify Your Account", $msg);
        
        header("Location: ../verify-otp.php?email=" . urlencode($email) . "&simulated_otp=" . $otp);
        exit;
    } else {
        header("Location: ../signup.php?error=registration_failed");
        exit;
    }
}

// ---------------------------------------------------------
// 2. VERIFY OTP
// ---------------------------------------------------------

if ($action == 'verify_otp' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitize_input($_POST['email']);
    $otp = sanitize_input($_POST['otp']);

    $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ? AND verification_code = ?");
    $stmt->execute([$email, $otp]);
    $user = $stmt->fetch();

    if ($user) {
        $upd = $pdo->prepare("UPDATE users SET is_email_verified = 1, verification_code = NULL WHERE id = ?");
        $upd->execute([$user['id']]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = strtolower($user['role']);
        
        $redirect = ($_SESSION['user_role'] === 'admin') ? "../admin/index.php" : "../index.php";
        header("Location: $redirect?success=account_verified");
        exit;
    } else {
        header("Location: ../verify-otp.php?email=" . urlencode($email) . "&error=invalid_otp");
        exit;
    }
}

// ---------------------------------------------------------
// 3. LOGIN
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $identifier = sanitize_input($_POST['identifier']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT id, name, email, password, role, is_email_verified FROM users WHERE email = ? OR username = ? OR phone = ?");
    $stmt->execute([$identifier, $identifier, $identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        if ($user['is_email_verified'] == 0) {
            $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            $pdo->prepare("UPDATE users SET verification_code = ? WHERE id = ?")->execute([$otp, $user['id']]);
            
            $msg = "Your OLX verification code is: <strong>$otp</strong>";
            send_auth_email($user['email'], "Verify Your Account", $msg);
            
            header("Location: ../verify-otp.php?email=" . urlencode($user['email']) . "&simulated_otp=" . $otp);
            exit;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = strtolower($user['role']);

        $redirect = ($_SESSION['user_role'] === 'admin') ? "../admin/index.php" : "../index.php";
        header("Location: $redirect");
        exit;
    } else {
        header("Location: ../login.php?error=invalid_credentials");
        exit;
    }
}

// ---------------------------------------------------------
// 4. GOOGLE AUTH
// ---------------------------------------------------------

if ($action == 'google_login') {
    $_SESSION['google_state'] = bin2hex(random_bytes(16));
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

if ($action == 'google_callback') {
    if (!isset($_GET['state']) || $_GET['state'] !== $_SESSION['google_state']) die('Invalid state.');

    if (isset($_GET['code'])) {
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri' => GOOGLE_REDIRECT_URL,
            'grant_type' => 'authorization_code',
            'code' => $_GET['code']
        ]));
        $token_data = json_decode(curl_exec($ch), true);
        curl_close($ch);
        
        if (isset($token_data['access_token'])) {
            $ch = curl_init("https://www.googleapis.com/oauth2/v2/userinfo?access_token=" . $token_data['access_token']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $profile = json_decode(curl_exec($ch), true);
            curl_close($ch);
            
            $email = $profile['email'];
            $name = $profile['name'];
            $username = strtolower(str_replace(' ', '', $name)) . rand(10, 99);

            $stmt = $pdo->prepare("SELECT id, name, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user) {
                $pass = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, is_email_verified) VALUES (?, ?, ?, ?, 1)");
                $stmt->execute([$name, $username, $email, $pass]);
                $user = ['id' => $pdo->lastInsertId(), 'name' => $name, 'role' => 'user'];
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = strtolower($user['role']);
            
            $redirect = ($_SESSION['user_role'] === 'admin') ? "../admin/index.php" : "../index.php";
            header("Location: $redirect");
            exit;
        }
    }
}

// 5. LOGOUT
if ($action == 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}