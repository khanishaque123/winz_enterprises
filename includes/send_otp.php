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

// Enable detailed logging
error_log("=== OTP REQUEST STARTED ===");
error_log("Time: " . date('Y-m-d H:i:s'));
error_log("POST Data: " . file_get_contents('php://input'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        error_log("Invalid JSON input");
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    if (!isset($input['email']) || !isset($input['type'])) {
        error_log("Missing email or type");
        echo json_encode(['success' => false, 'message' => 'Email and type are required']);
        exit;
    }
    
    $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
    $type = cleanInput($input['type']);
    
    error_log("Processing OTP request - Email: $email, Type: $type");
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email format: $email");
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    $name = '';
    
    if ($type === 'login') {
        // Check if customer exists
        $customer = getCustomerByEmail($email);
        if (!$customer) {
            error_log("Customer not found for login: $email");
            echo json_encode(['success' => false, 'message' => 'Email not registered. Please sign up first.']);
            exit;
        }
        $name = $customer['name'];
        error_log("Login OTP for existing customer: $name");
        
    } else if ($type === 'signup') {
        // Validate signup fields
        if (!isset($input['name']) || !isset($input['mobile']) || !isset($input['address'])) {
            error_log("Missing signup fields");
            echo json_encode(['success' => false, 'message' => 'All fields are required for signup']);
            exit;
        }
        
        $name = cleanInput($input['name']);
        $mobile = cleanInput($input['mobile']);
        $address = cleanInput($input['address']);
        
        error_log("Signup attempt - Name: $name, Mobile: $mobile");
        
        // Check if email already exists
        $existingCustomer = getCustomerByEmail($email);
        if ($existingCustomer) {
            error_log("Email already registered: $email");
            echo json_encode(['success' => false, 'message' => 'Email already registered']);
            exit;
        }
        
        // Validate mobile number
        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            error_log("Invalid mobile number: $mobile");
            echo json_encode(['success' => false, 'message' => 'Please enter a valid 10-digit mobile number']);
            exit;
        }
        
        // Store signup data in session for verification
        $_SESSION['signup_data'] = [
            'name' => $name,
            'email' => $email,
            'mobile' => $mobile,
            'address' => $address
        ];
        error_log("Signup data stored in session");
        
    } else {
        error_log("Invalid OTP type: $type");
        echo json_encode(['success' => false, 'message' => 'Invalid request type']);
        exit;
    }
    
    // Generate OTP
    $otp = generateOTP();
    error_log("Generated OTP: $otp for $email");
    
    // Store OTP in database
    if (!storeOTP($email, $otp, $type)) {
        error_log("Failed to store OTP in database");
        echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
        exit;
    }
    error_log("OTP stored in database successfully");
    
    // Store in session for backup verification
    if ($type === 'login') {
        $_SESSION['login_otp'] = $otp;
        $_SESSION['login_email'] = $email;
        $_SESSION['login_name'] = $name;
    } else {
        $_SESSION['signup_otp'] = $otp;
        $_SESSION['signup_email'] = $email;
    }
    $_SESSION['otp_expiry'] = time() + (OTP_EXPIRY_MINUTES * 60);
    
    // Always return success in debug mode with OTP
    if (DEBUG_MODE) {
        error_log("DEBUG MODE: Returning OTP without email sending");
        $response = [
            'success' => true, 
            'message' => 'OTP generated successfully!',

        ];
        echo json_encode($response);
        error_log("=== OTP REQUEST COMPLETED (DEBUG) ===");
        exit;
    }
    
    // Try to send email (only if not in debug mode)
    error_log("Attempting to send email to: $email");
   // In the email sending section, replace with:
$emailSent = sendOTPEmail($email, $name, $otp, $type);

// Always return success with OTP for now
$response = [
    'success' => true, 
    'message' => 'OTP generated successfully!',
    'debug_otp' => $otp // Always include OTP until email is fixed
];

if ($emailSent) {
    $response['message'] = 'OTP sent to your email!';
} else {
    $response['message'] = 'OTP generated! Check debug console.';
}

echo json_encode($response);
    }
    
?>