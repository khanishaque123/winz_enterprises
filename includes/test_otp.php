<?php
require_once 'db.php';

// Test email sending
$email = "ddkhank13@gmail.com";
$otp = "123456";
$type = "signup";

// Test database connection
try {
    $stmt = $pdo->query("SELECT 1");
    echo "Database connection: OK<br>";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . "<br>";
}

// Test email function
require_once 'send_otp.php';
$result = sendOtpEmail($email, $otp, $type);
echo "Email sending: " . ($result ? "OK" : "FAILED") . "<br>";

// Check if email log file exists
if (file_exists('email_log.txt')) {
    echo "Email log file exists and is writable<br>";
} else {
    echo "Email log file doesn't exist - creating test...<br>";
    file_put_contents('email_log.txt', "Test entry\n", FILE_APPEND);
}

echo "Test OTP: $otp<br>";
echo "Check your server's error_log for more details";
?>