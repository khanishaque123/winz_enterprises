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
    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        exit;
    }
    
    $username = trim($input['username']);
    $password = $input['password'];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        echo json_encode([
            'success' => false,
            'message' => 'Username and password cannot be empty'
        ]);
        exit;
    }
    
    if (strlen($password) < 6) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 6 characters long'
        ]);
        exit;
    }
    
    try {
        // Check if admin already exists
        $checkStmt = $pdo->prepare("SELECT id FROM admin WHERE username = ?");
        $checkStmt->execute([$username]);
        
        if ($checkStmt->fetch()) {
            echo json_encode([
                'success' => false,
                'message' => 'Admin username already exists'
            ]);
            exit;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new admin
        $stmt = $pdo->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $result = $stmt->execute([$username, $hashedPassword]);
        
        if ($result) {
            $adminId = $pdo->lastInsertId();
            
            echo json_encode([
                'success' => true,
                'message' => 'Admin user created successfully',
                'data' => [
                    'id' => $adminId,
                    'username' => $username,
                    'message' => 'Admin user created. You can now login with these credentials.'
                ]
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to create admin user'
            ]);
        }
        
    } catch (PDOException $e) {
        error_log("Database error in create_admin: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed'
    ]);
}
?>