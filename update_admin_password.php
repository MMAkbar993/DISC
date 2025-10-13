<?php
require_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = 'AdminDisc2024!@#';
    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    try {
        $query = "UPDATE admin_users SET password_hash = :password WHERE username = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':password', $passwordHash);
        
        if ($stmt->execute()) {
            echo "Admin password has been updated successfully!<br>";
            echo "New password: " . htmlspecialchars($newPassword) . "<br>";
            echo "Hash: " . $passwordHash . "<br>";
        } else {
            echo "Failed to update password.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    ?>
    <form method="post">
        <button type="submit">Update Admin Password</button>
    </form>
    <?php
}
?>
