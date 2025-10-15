<?php
// simple_fix.php
require_once 'includes/db.php';

try {
    $username = 'admin';
    $password = 'ishaque@321';
    
    // Fresh hash generate karo
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    echo "New Hash: <code>$hashed_password</code><br><br>";
    
    // Update password
    $sql = "UPDATE admin SET password = ? WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$hashed_password, $username]);
    
    echo "✅ Password updated!<br>";
    echo "Username: <strong>$username</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>