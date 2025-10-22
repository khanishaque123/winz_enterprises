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
    
    if (!isset($input['username']) || !isset($input['password'])) {
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit;
    }
    
    $username = cleanInput($input['username']);
    $password = cleanInput($input['password']);
    
    $admin = verifyAdmin($username, $password);
    
    if ($admin) {
        $_SESSION['user'] = [
            'id' => $admin['id'],
            'name' => $admin['name'],
            'username' => $admin['username'],
            'role' => 'admin'
        ];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => ['user' => $_SESSION['user']]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid admin credentials']);
    }
}
?>