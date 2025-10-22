<?php
require_once 'mail_config.php';
require_once 'functions.php';

echo "<h2>Email Configuration Test</h2>";

// Test basic configuration
echo "<h3>Configuration Check:</h3>";
echo "SMTP Host: " . SMTP_HOST . "<br>";
echo "SMTP Port: " . SMTP_PORT . "<br>";
echo "SMTP Username: " . SMTP_USERNAME . "<br>";
echo "From Email: " . SMTP_FROM_EMAIL . "<br>";
echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "<br>";

// Test email sending
echo "<h3>Email Sending Test:</h3>";
$test_email = "danishkkhan13@gmail.com";
$test_name = "Test User";
$test_otp = "123456";

$result = sendOTPEmail($test_email, $test_name, $test_otp, 'test');

if ($result) {
    echo "<p style='color: green;'>Email sent successfully!</p>";
} else {
    echo "<p style='color: red;'>Email sending failed. Check error logs.</p>";
}

// Test OTP generation
echo "<h3>OTP Generation Test:</h3>";
$otp = generateOTP();
echo "Generated OTP: " . $otp . "<br>";

// Test database connection
echo "<h3>Database Connection Test:</h3>";
$pdo = getDBConnection();
if ($pdo) {
    echo "<p style='color: green;'>Database connection successful!</p>";
} else {
    echo "<p style='color: red;'>Database connection failed!</p>";
}
?>