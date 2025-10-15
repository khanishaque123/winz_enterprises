<?php
// fresh_admin.php
require_once 'includes/db.php';

try {
    echo "<h2>Creating Fresh Admin User</h2>";
    
    // Pehle existing admin delete karo
    $pdo->exec("DELETE FROM admin WHERE username = 'admin'");
    
    // Naya admin banate hain
    $username = 'admin';
    $password = 'ishaque@321';
    
    // Fresh hash generate karo
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    echo "Username: <strong>$username</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "New Hash: <code>$hashed_password</code><br><br>";
    
    // Insert karo
    $sql = "INSERT INTO admin (username, password) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password]);
    
    echo "✅ Fresh admin user created!<br><br>";
    
    // Verify karo
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        echo "✅ Password verification SUCCESSFUL!<br>";
        echo "✅ Ab pakka login hoga!<br><br>";
        echo "<a href='admin/login.php' style='background: green; color: white; padding: 10px; text-decoration: none; border-radius: 5px;'>Click here to Login</a>";
    } else {
        echo "❌ Still not working!";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>