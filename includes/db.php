<?php
// includes/db.php
$host = 'localhost';
$dbname = 'admin_dashboard';
$username = 'postgres';
$password = 'Danish@321';
$port = '5433';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    // Don't output detailed errors in production
    if (isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Postman') !== false) {
        // For Postman requests, return JSON error
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed'
        ]);
    } else {
        echo "Database connection failed. Please try again later.";
    }
    exit;
}
?>