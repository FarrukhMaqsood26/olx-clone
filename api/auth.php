<?php
// api/auth.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../includes/config.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

/**
 * Returns a professionally designed HTML email template for OTP
 */
function get_auth_email_template($name, $otp) {
    return '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f9; color: #334155; margin: 0; padding: 0; }
            .container { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
            .header { background-color: #002f34; padding: 32px; text-align: center; }
            .header h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 800; letter-spacing: -0.025em; text-transform: uppercase; }
            .content { padding: 40px; text-align: center; }
            .content h2 { font-size: 20px; font-weight: 700; color: #0f172a; margin-top: 0; }
            .content p { font-size: 16px; line-height: 1.6; color: #64748b; margin-bottom: 24px; }
            .otp-box { background-color: #f1f5f9; border-radius: 12px; padding: 24px; margin: 24px 0; border: 2px dashed #002f34; }
            .otp-code { font-size: 32px; font-weight: 800; letter-spacing: 8px; color: #002f34; }
            .footer { padding: 24px; text-align: center; font-size: 13px; color: #94a3b8; background-color: #f8fafc; }
            .footer p { margin: 4px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>OLX CLONE</h1>
            </div>
            <div class="content">
                <h2>Hello, '.htmlspecialchars($name).'!</h2>
                <p>Welcome to Pakistan\'s largest local marketplace. To complete your account verification, please enter the following 6-digit code:</p>
                <div class="otp-box">
                    <div class="otp-code">'.$otp.'</div>
                </div>
                <p>Wait! If you didn\'t request this, simply ignore this email. This code is valid for a limited time only.</p>
            </div>
            <div class="footer">
                <p>&copy; 2026 OLX Clone. All rights reserved.</p>
                <p>This is an automated security notification. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Utility to send email via SMTP (PHPMailer)
 */
function send_auth_email($to, $subject, $htmlContent, $plainTextVersion = '') {
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return false;
    }
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;

        $mail->setFrom(SMTP_USER, 'OLX Clone Verification');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlContent;
        if (!empty($plainTextVersion)) {
            $mail->AltBody = $plainTextVersion;
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
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
    
    // Handle Avatar Upload
    $avatar_name = 'default.png';
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['avatar']['size'] <= 5 * 1024 * 1024) { // 5MB limit
            $avatar_name = time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir('../uploads/avatars/')) mkdir('../uploads/avatars/', 0777, true);
            move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/avatars/' . $avatar_name);
        }
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, phone, password, is_email_verified, verification_code, avatar) VALUES (?, ?, ?, ?, ?, 0, ?, ?)");
    if ($stmt->execute([$name, $username, $email, $phone, $password, $otp, $avatar_name])) {
        // Send Polished HTML OTP Email
        $htmlMsg = get_auth_email_template($name, $otp);
        $plainMsg = "Hello $name, your OLX Clone verification code is: $otp";
        send_auth_email($email, "Verify Your Account - $otp", $htmlMsg, $plainMsg);
        
        header("Location: ../verify-otp.php?email=" . urlencode($email));
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
            
            // Send Polished HTML OTP Email
            $htmlMsg = get_auth_email_template($user['name'], $otp);
            $plainMsg = "Hello " . $user['name'] . ", your OLX Clone verification code is: $otp";
            send_auth_email($user['email'], "Verify Your Account - $otp", $htmlMsg, $plainMsg);
            
            header("Location: ../verify-otp.php?email=" . urlencode($user['email']));
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

// ---------------------------------------------------------
// 5. UPDATE PROFILE
// ---------------------------------------------------------

if ($action == 'update_profile' && $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $name = sanitize_input($_POST['name']);
    $phone = sanitize_input($_POST['phone']);
    $new_password = $_POST['new_password'];

    $update_query = "UPDATE users SET name = ?, phone = ?";
    $params = [$name, $phone];

    if (!empty($new_password)) {
        $update_query .= ", password = ?";
        $params[] = password_hash($new_password, PASSWORD_DEFAULT);
    }

    // Handle File Upload
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['avatar']['size'] <= 5 * 1024 * 1024) {
            $avatar_name = time() . '_' . uniqid() . '.' . $ext;
            if (!is_dir('../uploads/avatars/')) mkdir('../uploads/avatars/', 0777, true);
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], '../uploads/avatars/' . $avatar_name)) {
                $update_query .= ", avatar = ?";
                $params[] = $avatar_name;
            }
        }
    }

    $update_query .= " WHERE id = ?";
    $params[] = $user_id;

    $stmt = $pdo->prepare($update_query);
    if ($stmt->execute($params)) {
        $_SESSION['user_name'] = $name;
        header("Location: ../profile.php?success=profile_updated");
    } else {
        header("Location: ../profile.php?error=update_failed");
    }
    exit;
}

// ---------------------------------------------------------
// 6. LOGOUT
// ---------------------------------------------------------
if ($action == 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}