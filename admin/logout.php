<?php
session_start();

// Log the logout activity
if (isset($_SESSION['admin_username'])) {
    $username = $_SESSION['admin_username'];
    error_log("Admin logout: User '{$username}' logged out at " . date('Y-m-d H:i:s'));
}

// Regenerate session ID to prevent session fixation
session_regenerate_id(true);

// Clear all session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Completely destroy the session
session_destroy();

// Set security headers
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to login with success message
header('Location: login.php?logout=success');
exit;
?>