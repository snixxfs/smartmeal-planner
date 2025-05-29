<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Clear existing admin users
    $conn->query("TRUNCATE TABLE admin");
    
    // Create new admin user
    $username = "admin";
    $password = "admin123";
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if ($stmt->execute()) {
        echo "Admin user created successfully!\n";
        echo "Username: " . $username . "\n";
        echo "Password: " . $password . "\n";
        echo "Hash: " . $hashed_password . "\n";
    } else {
        throw new Exception("Failed to create admin user: " . $stmt->error);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?> 