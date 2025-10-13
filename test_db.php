<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "Database connection successful!<br><br>";
    
    // Check if admin_users table exists
    try {
        $query = "SELECT * FROM admin_users WHERE username = 'admin' LIMIT 1";
        $stmt = $db->query($query);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "Admin user found:<br>";
            echo "ID: " . $admin['id'] . "<br>";
            echo "Username: " . $admin['username'] . "<br>";
            echo "Email: " . $admin['email'] . "<br>";
            echo "Is Active: " . $admin['is_active'] . "<br>";
            
            // Verify the password
            $testPassword = 'AdminDisc2024!@#';
            $isPasswordValid = password_verify($testPassword, $admin['password_hash']);
            echo "Password verification: " . ($isPasswordValid ? 'SUCCESS' : 'FAILED') . "<br>";
        } else {
            echo "No admin user found in the database.<br>";
        }
    } catch (PDOException $e) {
        echo "Error querying database: " . $e->getMessage() . "<br>";
    }
} else {
    echo "Failed to connect to the database.<br>";
}
?>
