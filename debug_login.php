<?php
// debug_login.php
require_once 'includes/db.php';

echo "<h2>Debug Login Issue</h2>";

try {
    // Check if admin user exists
    $stmt = $pdo->prepare('SELECT * FROM admin WHERE username = ?');
    $stmt->execute(['admin']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        echo "✅ Admin user found!<br>";
        echo "Username: " . $admin['username'] . "<br>";
        echo "Stored Hash: <code>" . $admin['password'] . "</code><br><br>";
        
        // Test different passwords
        $test_passwords = [
            'ishaque@321',
            'ishaqu@321',
            'ishaque321', 
            'Ishaque@321',
            'password',
            'admin'
        ];
        
        echo "<strong>Testing Passwords:</strong><br>";
        foreach ($test_passwords as $pwd) {
            $result = password_verify($pwd, $admin['password']) ? '✅ WORKS' : '❌ fails';
            echo "Password '<strong>$pwd</strong>': $result<br>";
        }
        
    } else {
        echo "❌ Admin user not found!<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>