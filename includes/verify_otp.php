<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['email']) || !isset($input['otp']) || !isset($input['type'])) {
        echo json_encode(['success' => false, 'message' => 'Email, OTP and type are required']);
        exit;
    }
    
    $email = cleanInput($input['email']);
    $otp = cleanInput($input['otp']);
    $type = cleanInput($input['type']);
    
    // Check if OTP exists and is not expired (session fallback)
    $sessionValid = false;
    if (isset($_SESSION['otp_expiry']) && time() > $_SESSION['otp_expiry']) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired']);
        exit;
    }
    
    // Verify OTP from database first
    $dbVerified = verifyOTP($email, $otp, $type);
    
    // Fallback to session verification
    if (!$dbVerified) {
        if ($type === 'signup') {
            $sessionOtp = $_SESSION['signup_otp'] ?? '';
            $sessionEmail = $_SESSION['signup_email'] ?? '';
        } else if ($type === 'login') {
            $sessionOtp = $_SESSION['login_otp'] ?? '';
            $sessionEmail = $_SESSION['login_email'] ?? '';
        }
        
        if ($email === $sessionEmail && $otp === $sessionOtp) {
            $sessionValid = true;
        }
        
        if (!$sessionValid) {
            echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP']);
            exit;
        }
    }
    
    if ($type === 'signup') {
        // Create customer account
        if (!isset($_SESSION['signup_data'])) {
            echo json_encode(['success' => false, 'message' => 'Signup data not found']);
            exit;
        }
        
        $signupData = $_SESSION['signup_data'];
        $customer = createCustomer($signupData['name'], $signupData['email'], $signupData['mobile'], $signupData['address']);
        
        if ($customer) {
            $_SESSION['user'] = [
                'id' => $customer['id'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'role' => 'customer'
            ];
            
            // Clear session data
            unset($_SESSION['signup_otp'], $_SESSION['signup_email'], $_SESSION['signup_data'], $_SESSION['otp_expiry']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Account created successfully!',
                'data' => ['user' => $_SESSION['user']]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create account. Please try again.']);
        }
    } else if ($type === 'login') {
        // Login customer
        $customer = getCustomerByEmail($email);
        
        if ($customer) {
            $_SESSION['user'] = [
                'id' => $customer['id'],
                'name' => $customer['name'],
                'email' => $customer['email'],
                'role' => 'customer'
            ];
            
            // Clear session data
            unset($_SESSION['login_otp'], $_SESSION['login_email'], $_SESSION['login_name'], $_SESSION['otp_expiry']);
            
            echo json_encode([
                'success' => true,
                'message' => 'Login successful!',
                'data' => ['user' => $_SESSION['user']]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Customer not found']);
        }
    }
}
?>