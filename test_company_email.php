<?php
require_once 'includes/mail_config.php';
require_once 'includes/functions.php';

echo "<h2>Elite Design Studio Email Test</h2>";

// Test configuration
echo "<h3>Current Configuration:</h3>";
echo "SMTP Host: " . SMTP_HOST . "<br>";
echo "SMTP Port: " . SMTP_PORT . "<br>";
echo "SMTP Username: " . SMTP_USERNAME . "<br>";
echo "From Email: " . SMTP_FROM_EMAIL . "<br>";
echo "DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "<br>";

// Test basic connectivity
echo "<h3>Connectivity Test:</h3>";
$connected = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
if ($connected) {
    echo "<p style='color: green;'>✅ Connected to " . SMTP_HOST . " on port " . SMTP_PORT . "</p>";
    fclose($connected);
} else {
    echo "<p style='color: red;'>❌ Cannot connect to " . SMTP_HOST . " on port " . SMTP_PORT . " - $errstr ($errno)</p>";
}

// Test email sending
echo "<h3>Email Sending Test:</h3>";

$test_email = 'ddkhank13@gmail.com'; // Your test email
$test_name = 'Test User';
$otp = generateOTP();

echo "Testing email to: $test_email<br>";
echo "Generated OTP: <strong>$otp</strong><br>";

// Test in debug mode first
if (DEBUG_MODE) {
    echo "<p style='color: orange;'>⚠️ DEBUG MODE: Email won't be sent, but OTP is generated</p>";
    echo "<p>In production (DEBUG_MODE = false), email would be sent to: $test_email</p>";
}

$result = sendOTPEmail($test_email, $test_name, $otp, 'test');

if ($result) {
    echo "<p style='color: green;'>✅ Email function executed successfully!</p>";
} else {
    echo "<p style='color: red;'>❌ Email function failed!</p>";
}

// Test database connection
echo "<h3>Database Test:</h3>";
$pdo = getDBConnection();
if ($pdo) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test OTP storage
    $storageTest = storeOTP($test_email, $otp, 'test');
    if ($storageTest) {
        echo "<p style='color: green;'>✅ OTP storage in database successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ OTP storage in database failed!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
}

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "1. Check if DEBUG_MODE is true (shows OTP on screen)<br>";
echo "2. Set DEBUG_MODE to false to test actual email sending<br>";
echo "3. Check email spam folder if not receiving emails<br>";
echo "4. Verify SMTP credentials with your hosting provider<br>";
?>