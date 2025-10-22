<?php
// test_email_detailed.php - Detailed Email Diagnostic Tool
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Diagnostic Tool - WInz Enterprises</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            line-height: 1.6;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1, h2, h3 {
            color: #333;
        }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        .debug-output {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .test-form {
            background: #e9f7fe;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .test-form input {
            padding: 8px;
            margin: 5px;
            width: 300px;
        }
        .test-form button {
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .test-form button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Diagnostic Tool</h1>
        <p>This tool will test your email configuration with detailed SMTP debugging.</p>

        <div class="test-form">
            <h3>Test Email Configuration</h3>
            <form method="POST">
                <div>
                    <label>Test Email Address:</label><br>
                    <input type="email" name="test_email" value="ddkhank13@gmail.com" required>
                </div>
                <div>
                    <label>Test Name:</label><br>
                    <input type="text" name="test_name" value="Test User" required>
                </div>
                <div>
                    <button type="submit" name="run_test">Run Email Test</button>
                </div>
            </form>
        </div>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_test'])) {
            runEmailTest();
        }

        function runEmailTest() {
            echo "<h2>🧪 Running Email Test...</h2>";
            
            // Load configuration and functions
            require_once 'includes/mail_config.php';
            require_once 'includes/functions.php';

            $test_email = $_POST['test_email'];
            $test_name = $_POST['test_name'];
            $otp = generateOTP();

            echo "<h3>Test Details:</h3>";
            echo "<p><strong>Test Email:</strong> $test_email</p>";
            echo "<p><strong>Test Name:</strong> $test_name</p>";
            echo "<p><strong>Generated OTP:</strong> <span class='warning'>$otp</span></p>";

            echo "<h3>Current Configuration:</h3>";
            echo "<ul>";
            echo "<li>SMTP Host: " . SMTP_HOST . "</li>";
            echo "<li>SMTP Port: " . SMTP_PORT . "</li>";
            echo "<li>SMTP Username: " . SMTP_USERNAME . "</li>";
            echo "<li>From Email: " . SMTP_FROM_EMAIL . "</li>";
            echo "<li>DEBUG_MODE: " . (DEBUG_MODE ? 'true' : 'false') . "</li>";
            echo "</ul>";

            echo "<h3>SMTP Debug Output:</h3>";
            echo "<div class='debug-output'>";

            // Capture the output for SMTP debug
            ob_start();

            try {
                // Check if PHPMailer files exist
                $phpmailerPath = 'includes/PHPMailer/';
                if (!file_exists($phpmailerPath . 'PHPMailer.php')) {
                    throw new Exception("PHPMailer files not found at: $phpmailerPath");
                }

                require_once $phpmailerPath . 'PHPMailer.php';
                require_once $phpmailerPath . 'SMTP.php';
                require_once $phpmailerPath . 'Exception.php';

                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                
                // Server settings with detailed debugging
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USERNAME;
                $mail->Password = SMTP_PASSWORD;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;
                $mail->SMTPDebug = 2; // Full debug output
                $mail->Debugoutput = function($str, $level) {
                    echo "SMTP [$level]: $str\n";
                };
                
                // Timeout settings
                $mail->Timeout = 30;
                
                // SSL options
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
                
                // Recipients
                $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                $mail->addAddress($test_email, $test_name);
                $mail->addReplyTo(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
                
                // Email content
                $mail->Subject = 'Test Email from WInz Enterprises - Diagnostic';
                $mail->Body = "
                    <h1>Test Email Successful!</h1>
                    <p>This is a test email from WInz Enterprises diagnostic tool.</p>
                    <p><strong>Generated OTP: $otp</strong></p>
                    <p>If you received this email, your SMTP configuration is working correctly.</p>
                    <hr>
                    <p><small>Test conducted on: " . date('Y-m-d H:i:s') . "</small></p>
                ";
                
                $mail->isHTML(true);
                
                $result = $mail->send();
                
                if ($result) {
                    echo "\n\n<span class='success'>✅ EMAIL SENT SUCCESSFULLY!</span>";
                } else {
                    echo "\n\n<span class='error'>❌ EMAIL SENDING FAILED!</span>";
                }
                
            } catch (Exception $e) {
                echo "\n\n<span class='error'>❌ EXCEPTION: " . $e->getMessage() . "</span>";
                error_log("Email Diagnostic Exception: " . $e->getMessage());
            }

            $debugOutput = ob_get_clean();
            echo htmlspecialchars($debugOutput);
            echo "</div>";

            // Additional diagnostics
            echo "<h3>Additional Diagnostics:</h3>";
            
            // Test basic connectivity
            echo "<p><strong>SMTP Connection Test:</strong> ";
            $connected = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
            if ($connected) {
                echo "<span class='success'>✅ Connected to " . SMTP_HOST . " on port " . SMTP_PORT . "</span>";
                fclose($connected);
            } else {
                echo "<span class='error'>❌ Cannot connect to " . SMTP_HOST . " on port " . SMTP_PORT . " - $errstr ($errno)</span>";
            }
            echo "</p>";

            // Check if email function exists
            echo "<p><strong>Email Function Test:</strong> ";
            if (function_exists('sendOTPEmail')) {
                echo "<span class='success'>✅ sendOTPEmail function exists</span>";
            } else {
                echo "<span class='error'>❌ sendOTPEmail function not found</span>";
            }
            echo "</p>";

            echo "<hr>";
            echo "<h3>Next Steps:</h3>";
            echo "<ol>";
            echo "<li><strong>Check the SMTP debug output above</strong> - look for authentication success/failure</li>";
            echo "<li><strong>Check your email inbox AND spam folder</strong> for the test email</li>";
            echo "<li><strong>Verify SMTP credentials</strong> with your hosting provider if authentication fails</li>";
            echo "<li><strong>Try different SMTP ports</strong> (587, 465, 25) if connection fails</li>";
            echo "<li><strong>Check server error logs</strong> for additional PHP errors</li>";
            echo "</ol>";

            echo "<div class='test-form'>";
            echo "<h3>Test Again</h3>";
            echo "<p>If you made changes to your configuration, test again:</p>";
            echo "<form method='POST'>";
            echo "<input type='email' name='test_email' value='$test_email' required>";
            echo "<input type='text' name='test_name' value='$test_name' required>";
            echo "<button type='submit' name='run_test'>Run Test Again</button>";
            echo "</form>";
            echo "</div>";
        }
        ?>

        <hr>
        <h2>Common SMTP Issues & Solutions</h2>
        
        <h3>🔑 Authentication Issues:</h3>
        <ul>
            <li><strong>Incorrect password</strong> - Double-check your email password</li>
            <li><strong>Wrong username</strong> - Use full email address as username</li>
            <li><strong>App passwords</strong> - For Gmail, use App Password instead of regular password</li>
        </ul>

        <h3>🌐 Connection Issues:</h3>
        <ul>
            <li><strong>Wrong port</strong> - Try 587 (TLS), 465 (SSL), or 25 (plain)</li>
            <li><strong>Firewall blocking</strong> - Check if your hosting provider blocks outbound SMTP</li>
            <li><strong>Wrong hostname</strong> - Verify the exact SMTP server name</li>
        </ul>

        <h3>📧 Delivery Issues:</h3>
        <ul>
            <li><strong>Check spam folder</strong> - Emails often go to spam during testing</li>
            <li><strong>Domain reputation</strong> - New domains may have delivery issues</li>
            <li><strong>SPF records</strong> - Ensure your domain has proper SPF records</li>
        </ul>

        <div class="warning">
            <h3>⚠️ Security Notice</h3>
            <p>This diagnostic tool reveals sensitive SMTP information. <strong>Delete this file after testing</strong> to prevent security risks.</p>
        </div>
    </div>
</body>
</html>