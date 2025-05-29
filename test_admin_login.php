<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Test credentials
    $username = "admin";
    $password = "admin123";
    
    echo "Testing admin login with:\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n\n";
    
    // Get admin user
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Admin user not found");
    }
    
    $admin = $result->fetch_assoc();
    echo "Found admin user:\n";
    echo "ID: " . $admin['id'] . "\n";
    echo "Username: " . $admin['username'] . "\n";
    echo "Stored hash: " . $admin['password'] . "\n\n";
    
    // Test password verification
    if (password_verify($password, $admin['password'])) {
        echo "✅ Password verification successful!\n";
    } else {
        echo "❌ Password verification failed!\n";
        
        // Create a new hash for comparison
        $new_hash = password_hash($password, PASSWORD_DEFAULT);
        echo "\nNew hash for comparison: " . $new_hash . "\n";
        
        // Test the new hash
        if (password_verify($password, $new_hash)) {
            echo "✅ New hash verification successful!\n";
        } else {
            echo "❌ New hash verification failed!\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?> 