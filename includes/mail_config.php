<?php
// includes/mail_config.php

// Company Email Configuration - Elite Design Studio
define('SMTP_HOST', 'mail.elitedesignstudio.co.in');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'webmaster@elitedesignstudio.co.in');
define('SMTP_PASSWORD', 'Elitestudio@321');
define('SMTP_FROM_EMAIL', 'webmaster@elitedesignstudio.co.in');
define('SMTP_FROM_NAME', 'WInz Enterprises');

// OTP Configuration
define('OTP_EXPIRY_MINUTES', 5);
define('OTP_LENGTH', 6);

// Enable debug mode for testing - SET TO FALSE FOR REAL EMAILS
define('DEBUG_MODE', false); // Changed to false to send real emails

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_PORT', '5433');
define('DB_NAME', 'admin_dashboard');
define('DB_USER', 'postgres');
define('DB_PASS', 'Danish@321');

// Enhanced email settings
define('SMTP_SECURE', 'tls');
define('SMTP_TIMEOUT', 30);
?>