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
                <h1>Bazaar</h1>
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
                <p>&copy; 2026 Bazaar. All rights reserved.</p>
                <p>This is an automated security notification. Please do not reply.</p>
            </div>
        </div>
    </body>
    </html>';
}

/**
 * Returns a professionally designed HTML email template for Password Reset
 */
function get_reset_password_email_template($name, $resetLink) {
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
            .content p { font-size: 16px; line-height: 1.6; color: #64748b; margin-bottom: 32px; }
            .reset-btn { background-color: #002f34; color: #ffffff; padding: 16px 32px; border-radius: 8px; text-decoration: none; font-weight: 700; display: inline-block; transition: background-color 0.2s; }
            .reset-btn:hover { background-color: #004d53; }
            .footer { padding: 24px; text-align: center; font-size: 13px; color: #94a3b8; background-color: #f8fafc; }
            .footer p { margin: 4px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Bazaar</h1>
            </div>
            <div class="content">
                <h2>Reset Your Password</h2>
                <p>Hello '.htmlspecialchars($name).', we received a request to reset your Bazaar account password. Click the button below to set a new password:</p>
                <a href="'.$resetLink.'" class="reset-btn">Reset Password</a>
                <p style="margin-top: 32px; font-size: 13px;">If you didn\'t request this, you can safely ignore this email. This link will expire in 1 hour.</p>
            </div>
            <div class="footer">
                <p>&copy; 2026 Bazaar. All rights reserved.</p>
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
        
        // Skip SSL verification for local development (common XAMPP issue)
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom(SMTP_USER, 'Bazaar Verification');
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

/**
 * Validates a Pakistan phone number
 * Accepts formats: 03xxxxxxxxx, +923xxxxxxxxx, 923xxxxxxxxx
 */
function is_valid_pakistan_number($phone) {
    // Remove any dashes or spaces
    $phone = str_replace(['-', ' '], '', $phone);
    // Regex for Pakistan mobile numbers
    // Allows 03xx, 923xx, +923xx or 00923xx followed by 7-9 digits (standard is 7 after the 03xx)
    return preg_match('/^((\+92)|(0092)|(92)|(0))3\d{9}$/', $phone);
}

// ---------------------------------------------------------
// 1. SIGNUP
// ---------------------------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signup'])) {
    $name = sanitize_input($_POST['name']);
    $username = sanitize_input($_POST['username']);
    $email = sanitize_input($_POST['email']);
    $phone = sanitize_input($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Validate Pakistan Phone Number
    if (!is_valid_pakistan_number($phone)) {
        header("Location: ../signup.php?error=invalid_phone");
        exit;
    }

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

    try {
        $pdo->beginTransaction();
        
        // DEBUG: Log received POST keys
        file_put_contents('auth_debug.txt', "Received POST keys: " . implode(', ', array_keys($_POST)) . "\n", FILE_APPEND);
        if (isset($_POST['scan_front_image'])) {
            file_put_contents('auth_debug.txt', "Front Image Length: " . strlen($_POST['scan_front_image']) . "\n", FILE_APPEND);
        }

        $stmt = $pdo->prepare("
            INSERT INTO users (
                name, username, email, phone, password,
                is_email_verified, verification_code, avatar
            ) VALUES (?, ?, ?, ?, ?, 0, ?, ?)
        ");
        $stmt->execute([
            $name, $username, $email, $phone, $password, $otp, $avatar_name
        ]);
        
        $userId = (int)$pdo->lastInsertId();

        // Save Face Scans
        $scan_angles = ['front', 'left', 'right', 'up', 'down'];
        $landmarks_json = isset($_POST['scan_landmarks']) ? $_POST['scan_landmarks'] : null;
        $landmarks_array = $landmarks_json ? json_decode($landmarks_json, true) : [];

        foreach ($scan_angles as $angle) {
            $post_key = "scan_{$angle}_image";
            if (isset($_POST[$post_key]) && !empty($_POST[$post_key])) {
                $base64Data = $_POST[$post_key];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $data = base64_decode($data);
                    
                    $scan_filename = 'scan_' . $userId . '_' . $angle . '_' . time() . '.jpg';
                    $scan_dir = '../uploads/face-scans/';
                    if (!is_dir($scan_dir)) mkdir($scan_dir, 0777, true);
                    
                    if (file_put_contents($scan_dir . $scan_filename, $data)) {
                        $angleLandmarks = isset($landmarks_array[$angle]) ? json_encode($landmarks_array[$angle]) : null;
                        
                        $pdo->prepare("
                            INSERT INTO user_face_scans (user_id, capture_type, image_path, mesh_landmarks_json, capture_angle)
                            VALUES (?, 'face_mesh', ?, ?, ?)
                        ")->execute([$userId, $scan_filename, $angleLandmarks, $angle]);
                    }
                }
            }
        }
        
        $pdo->commit();
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Signup failed: ' . $e->getMessage());
        header("Location: ../signup.php?error=registration_failed");
        exit;
    }

    // Send Polished HTML OTP Email
    $htmlMsg = get_auth_email_template($name, $otp);
    $plainMsg = "Hello $name, your Bazaar verification code is: $otp";
    send_auth_email($email, "Verify Your Account - $otp", $htmlMsg, $plainMsg);

    header("Location: ../verify-otp.php?email=" . urlencode($email));
    exit;
}

// 1.1 Complete Face Verification (For Google Users or those who skipped)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['complete_face_verification'])) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
    
    $userId = (int)$_SESSION['user_id'];
    
    try {
        $pdo->beginTransaction();
        
        $scan_angles = ['front', 'left', 'right', 'up', 'down'];
        $landmarks_json = isset($_POST['scan_landmarks']) ? $_POST['scan_landmarks'] : null;
        $landmarks_array = $landmarks_json ? json_decode($landmarks_json, true) : [];

        foreach ($scan_angles as $angle) {
            $post_key = "scan_{$angle}_image";
            if (isset($_POST[$post_key]) && !empty($_POST[$post_key])) {
                $base64Data = $_POST[$post_key];
                if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                    $data = substr($base64Data, strpos($base64Data, ',') + 1);
                    $data = base64_decode($data);
                    
                    $scan_filename = 'scan_' . $userId . '_' . $angle . '_' . time() . '.jpg';
                    $scan_dir = '../uploads/face-scans/';
                    if (!is_dir($scan_dir)) mkdir($scan_dir, 0777, true);
                    
                    if (file_put_contents($scan_dir . $scan_filename, $data)) {
                        $angleLandmarks = isset($landmarks_array[$angle]) ? json_encode($landmarks_array[$angle]) : null;
                        
                        $pdo->prepare("
                            INSERT INTO user_face_scans (user_id, capture_type, image_path, mesh_landmarks_json, capture_angle)
                            VALUES (?, 'face_mesh', ?, ?, ?)
                        ")->execute([$userId, $scan_filename, $angleLandmarks, $angle]);
                    }
                }
            }
        }
        $pdo->commit();
        header("Location: ../index.php?success=verified");
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        header("Location: ../face-verify.php?error=verification_failed");
    }
    exit;
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
            $plainMsg = "Hello " . $user['name'] . ", your Bazaar verification code is: $otp";
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
                // Auto-create account for Google users
                $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, is_email_verified, avatar) VALUES (?, ?, ?, ?, 1, 'default.png')");
                $stmt->execute([$name, $username, $email, password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT)]);
                $userId = (int)$pdo->lastInsertId();
                
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = strtolower($user['role']);
            }
            
            // Check if user has completed face verification
            $checkScan = $pdo->prepare("SELECT COUNT(*) FROM user_face_scans WHERE user_id = ?");
            $checkScan->execute([$_SESSION['user_id']]);
            $hasScan = $checkScan->fetchColumn();

            if (!$hasScan) {
                header("Location: ../face-verify.php");
            } else {
                $redirect = ($_SESSION['user_role'] === 'admin') ? "../admin/index.php" : "../index.php";
                header("Location: $redirect");
            }
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

    // Validate Pakistan Phone Number
    if (!is_valid_pakistan_number($phone)) {
        header("Location: ../profile.php?error=invalid_phone");
        exit;
    }

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
// 6. FORGOT PASSWORD
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'forgot_password') {
    $email = sanitize_input($_POST['email']);
    
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
        
        $upd = $pdo->prepare("UPDATE users SET reset_token = ?, reset_expiry = ? WHERE id = ?");
        $upd->execute([$token, $expiry, $user['id']]);
        
        // Construct Link
        $resetLink = "http://localhost/bazaar/reset-password.php?token=" . $token;
        $htmlMsg = get_reset_password_email_template($user['name'], $resetLink);
        $plainMsg = "Hello " . $user['name'] . ", reset your password here: " . $resetLink;
        
        send_auth_email($email, "Reset Your Account Password", $htmlMsg, $plainMsg);
    }
    
    // Always redirect to a "check your email" success page for security
    header("Location: ../forgot-password.php?success=email_sent");
    exit;
}

// ---------------------------------------------------------
// 7. RESET PASSWORD (Final Submission)
// ---------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] == 'reset_password') {
    $token = sanitize_input($_POST['token']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        $upd = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE id = ?");
        $upd->execute([$password, $user['id']]);
        header("Location: ../login.php?success=password_reset");
    } else {
        header("Location: ../forgot-password.php?error=invalid_token");
    }
    exit;
}

// ---------------------------------------------------------
// 8. LOGOUT
// ---------------------------------------------------------
if ($action == 'logout') {
    session_unset();
    session_destroy();
    setcookie('recently_viewed', '', time() - 3600, '/');
    header("Location: ../index.php");
    exit;
}