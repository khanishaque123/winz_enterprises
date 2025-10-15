<?php
// test_connection.php
require_once 'includes/db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test connection
    echo "✅ Connected to PostgreSQL successfully!<br>";
    
    // Show database info
    echo "Database: " . $pdo->query("SELECT current_database()")->fetchColumn() . "<br>";
    echo "User: " . $pdo->query("SELECT current_user")->fetchColumn() . "<br>";
    echo "PostgreSQL Version: " . $pdo->query("SELECT version()")->fetchColumn() . "<br>";
    
    // Check if admin table exists
    $tables = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'admin'")->fetch();
    
    if ($tables) {
        echo "✅ Admin table exists<br>";
        
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute(['ishaque123']);
        $admin = $stmt->fetch();
        
        if ($admin) {
            echo "✅ Admin user 'ishaque123' exists<br>";
        } else {
            echo "❌ Admin user 'ishaque123' not found<br>";
        }
    } else {
        echo "❌ Admin table does not exist - run setup_admin.php<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>