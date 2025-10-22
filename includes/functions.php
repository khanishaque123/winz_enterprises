<?php
// includes/functions.php
require_once 'mail_config.php';

// Database connection
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "pgsql:host=" . DB_HOST . 
                ";port=" . DB_PORT . 
                ";dbname=" . DB_NAME, 
                DB_USER, 
                DB_PASS
            );
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            return false;
        }
    }
    
    return $pdo;
}

// Generate OTP
function generateOTP($length = OTP_LENGTH) {
    return str_pad(rand(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

// Clean input data
function cleanInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Get customer by email
function getCustomerByEmail($email) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting customer by email: " . $e->getMessage());
        return false;
    }
}

// Create new customer
function createCustomer($name, $email, $mobile, $address) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, mobile, address, is_verified, created_at) VALUES (?, ?, ?, ?, true, NOW()) RETURNING id, name, email, mobile, address, created_at");
        $stmt->execute([$name, $email, $mobile, $address]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error creating customer: " . $e->getMessage());
        return false;
    }
}

// Store OTP in database (for tracking)
function storeOTP($email, $otp, $type) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $expiry = date('Y-m-d H:i:s', time() + (OTP_EXPIRY_MINUTES * 60));
        $stmt = $pdo->prepare("INSERT INTO otp_verifications (email, otp, type, expiry, created_at) VALUES (?, ?, ?, ?, NOW())");
        return $stmt->execute([$email, $otp, $type, $expiry]);
    } catch (PDOException $e) {
        error_log("Error storing OTP: " . $e->getMessage());
        return false;
    }
}

// Verify OTP from database
function verifyOTP($email, $otp, $type) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM otp_verifications WHERE email = ? AND otp = ? AND type = ? AND expiry > NOW() AND used = false ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $otp, $type]);
        $otpRecord = $stmt->fetch();
        
        if ($otpRecord) {
            // Mark OTP as used
            $updateStmt = $pdo->prepare("UPDATE otp_verifications SET used = true, used_at = NOW() WHERE id = ?");
            $updateStmt->execute([$otpRecord['id']]);
            return true;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error verifying OTP: " . $e->getMessage());
        return false;
    }
}

// Send OTP Email
function sendOTPEmail($email, $name, $otp, $type = 'login') {
    if (DEBUG_MODE) {
        error_log("DEBUG OTP for $email ($name): $otp - Type: $type");
        return true;
    }
    
    // Check if PHPMailer files exist
    $phpmailerPath = __DIR__ . '/PHPMailer/';
    if (!file_exists($phpmailerPath . 'PHPMailer.php') || 
        !file_exists($phpmailerPath . 'SMTP.php') || 
        !file_exists($phpmailerPath . 'Exception.php')) {
        error_log("PHPMailer files not found at: $phpmailerPath");
        return false;
    }
    
    require_once $phpmailerPath . 'PHPMailer.php';
    require_once $phpmailerPath . 'SMTP.php';
    require_once $phpmailerPath . 'Exception.php';
    
    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->SMTPDebug = 0; // Set to 2 for debugging if needed
        $mail->Debugoutput = 'error_log';
        
        // Timeout settings
        $mail->Timeout = 30;
        
        // SSL context options for better compatibility
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        
        // Recipients
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $name);
        $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        
        // Email content
        $subject = $type === 'login' ? 'Your Login OTP - WInz Enterprises' : 'Your Signup OTP - WInz Enterprises';
        $mail->Subject = $subject;
        
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        line-height: 1.6; 
                        color: #333; 
                        margin: 0; 
                        padding: 0; 
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        padding: 20px; 
                        border: 1px solid #ddd; 
                        border-radius: 10px; 
                        background: #ffffff;
                    }
                    .header { 
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                        color: white; 
                        padding: 20px; 
                        text-align: center; 
                        border-radius: 10px 10px 0 0; 
                    }
                    .content { 
                        padding: 30px 20px; 
                    }
                    .otp-code { 
                        font-size: 32px; 
                        font-weight: bold; 
                        color: #667eea; 
                        text-align: center; 
                        margin: 30px 0; 
                        padding: 15px; 
                        background: #f7fafc; 
                        border-radius: 8px; 
                        letter-spacing: 5px; 
                        border: 2px dashed #667eea;
                    }
                    .footer { 
                        margin-top: 30px; 
                        padding-top: 20px; 
                        border-top: 1px solid #eee; 
                        font-size: 12px; 
                        color: #718096; 
                        text-align: center; 
                    }
                    .warning { 
                        background: #fff3cd; 
                        border: 1px solid #ffeaa7; 
                        border-radius: 5px; 
                        padding: 15px; 
                        margin: 20px 0; 
                        color: #856404; 
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>WInz Enterprises</h1>
                    </div>
                    <div class='content'>
                        <h2>" . ($type === 'login' ? 'Login OTP' : 'Welcome to WInz Enterprises!') . "</h2>
                        <p>Hello <strong>$name</strong>,</p>
                        <p>" . ($type === 'login' ? 'Your One-Time Password (OTP) for login is:' : 'Thank you for choosing WInz Enterprises. Your OTP for account verification is:') . "</p>
                        <div class='otp-code'>$otp</div>
                        <div class='warning'>
                            <strong>Important:</strong> This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes. Do not share this OTP with anyone.
                        </div>
                        <p>If you didn't request this OTP, please ignore this email.</p>
                    </div>
                    <div class='footer'>
                        <p>&copy; 2023 WInz Enterprises. All rights reserved.</p>
                        <p>This is an automated email, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        // Plain text version for email clients that don't support HTML
        $mail->AltBody = "
            WInz Enterprises - " . ($type === 'login' ? 'Login OTP' : 'Welcome OTP') . "
            
            Hello $name,
            
            Your One-Time Password (OTP) is: $otp
            
            This OTP will expire in " . OTP_EXPIRY_MINUTES . " minutes.
            
            If you didn't request this OTP, please ignore this email.
            
            Best regards,
            WInz Enterprises Team
        ";
        
        $mail->isHTML(true);
        
        // Additional headers for better deliverability
        $mail->addCustomHeader('X-Priority', '1');
        $mail->addCustomHeader('X-Mailer', 'PHP/' . phpversion());
        
        $result = $mail->send();
        
        if ($result) {
            error_log("✅ Email sent successfully to $email");
            return true;
        } else {
            error_log("❌ Email sending failed to $email");
            return false;
        }
        
    } catch (Exception $e) {
        error_log("❌ PHPMailer Error for $email: " . $e->getMessage());
        return false;
    }
}

// Clean expired OTPs (optional cleanup function)
function cleanExpiredOTPs() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("DELETE FROM otp_verifications WHERE expiry < NOW() - INTERVAL '1 day'");
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error cleaning expired OTPs: " . $e->getMessage());
        return false;
    }
}

// Verify admin credentials
function verifyAdmin($username, $password) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            return $admin;
        }
        return false;
    } catch (PDOException $e) {
        error_log("Error verifying admin: " . $e->getMessage());
        return false;
    }
}

// Product functions
function addProduct($name, $price, $description, $image = null) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, price, description, image) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $price, $description, $image]);
    } catch (PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

function getProducts() {
    $pdo = getDBConnection();
    if (!$pdo) return [];
    
    try {
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting products: " . $e->getMessage());
        return [];
    }
}

function getProductById($id) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting product by ID: " . $e->getMessage());
        return false;
    }
}

function updateProduct($id, $name, $price, $description, $image = null) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        if ($image) {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ?, image = ? WHERE id = ?");
            return $stmt->execute([$name, $price, $description, $image, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, price = ?, description = ? WHERE id = ?");
            return $stmt->execute([$name, $price, $description, $id]);
        }
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

function deleteProduct($id) {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    try {
        // Get product info to delete image file
        $product = getProductById($id);
        if ($product && $product['image']) {
            $imagePath = "uploads/" . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        error_log("Error deleting product: " . $e->getMessage());
        return false;
    }
}

function uploadImage($file) {
    $targetDir = "uploads/";
    
    // Create uploads directory if it doesn't exist
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file["name"]);
    $targetFile = $targetDir . $fileName;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return ["success" => false, "message" => "File is not an image."];
    }
    
    // Check file size (5MB limit)
    if ($file["size"] > 5000000) {
        return ["success" => false, "message" => "File is too large. Maximum size is 5MB."];
    }
    
    // Allow certain file formats
    $allowedFormats = ["jpg", "jpeg", "png", "gif", "webp"];
    if (!in_array($imageFileType, $allowedFormats)) {
        return ["success" => false, "message" => "Only JPG, JPEG, PNG, GIF & WEBP files are allowed."];
    }
    
    // Upload file
    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        return ["success" => true, "fileName" => $fileName];
    } else {
        return ["success" => false, "message" => "Error uploading file."];
    }
}
?>