<?php
// includes/db.php
$host = 'localhost';
$dbname = 'postgres'; // Changed from 'postgres' to 'postgres'
$username = 'postgres';
$password = 'ishaque@321';
$port = '5432';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "PostgreSQL Connection Failed: " . $e->getMessage();
    $error_message .= "<br><br>To fix this:";
    $error_message .= "<br>1. Make sure PostgreSQL is running on port 5432";
    $error_message .= "<br>2. Check if database 'postgres' exists";
    $error_message .= "<br>3. Verify username/password";
    $error_message .= "<br>4. Enable pdo_pgsql extension in php.ini";
    die($error_message);
}
?>