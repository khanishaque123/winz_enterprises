<?php
// complete_reset.php
require_once 'includes/db.php';

try {
    echo "<h2>Complete Admin Reset</h2>";
    
    // Drop and recreate table
    $pdo->exec('DROP TABLE IF EXISTS admin');
    
    $pdo->exec('
        CREATE TABLE admin (
            id SERIAL PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ');
    
    echo "✅ Table recreated<br>";
    
    // Insert fresh admin with correct hash
    $username = 'admin';
    $password = 'ishaque@321';
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password]);
    
    echo "✅ Admin user inserted<br>";
    echo "Username: <strong>$username</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "Hash: <code>$hashed_password</code><br><br>";
    
    // Verify
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if (password_verify($password, $admin['password'])) {
        echo "✅ Password verification SUCCESSFUL!<br>";
        echo "✅ Ab pakka login hoga!";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>