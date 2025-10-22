<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $requiredFields = ['name', 'email', 'mobile', 'address'];
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $missingFields[] = $field;
        }
    }
    
    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required fields: ' . implode(', ', $missingFields)
        ]);
        exit;
    }
    
    $name = trim($input['name']);
    $email = trim($input['email']);
    $mobile = trim($input['mobile']);
    $address = trim($input['address']);
    
    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email format'
        ]);
        exit;
    }
    
    // Mobile validation (basic 10-digit check)
    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        echo json_encode([
            'success' => false,
            'message' => 'Mobile number must be 10 digits'
        ]);
        exit;
    }
    
    try {
        // Check if customer already exists with same email
        $checkStmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $checkStmt->execute([$email]);
        
        if ($checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Customer with this email already exists'
            ]);
            exit;
        }
        
        // Insert new customer
        $stmt = $pdo->prepare("INSERT INTO customers (name, email, mobile, address) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$name, $email, $mobile, $address]);
        
        if ($result) {
            $customerId = $pdo->lastInsertId();
            
            // Get the created customer data
            $getStmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $getStmt->execute([$customerId]);
            $customer = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Customer created successfully',
                'data' => [
                    'customer' => [
                        'id' => $customer['id'],
                        'name' => $customer['name'],
                        'email' => $customer['email'],
                        'mobile' => $customer['mobile'],
                        'address' => $customer['address'],
                        'created_at' => $customer['created_at']
                    ]
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create customer'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in create_customer: " . $e->getMessage());
        
        // Check for unique constraint violation
        if ($e->getCode() == 23505) { // PostgreSQL unique violation code
            echo json_encode([
                'success' => false,
                'message' => 'Customer with this email already exists'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
}
?>