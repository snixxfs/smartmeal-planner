<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    echo "Listing all users:\n\n";
    
    // Get all users
    $result = $conn->query("SELECT id, username, created_at FROM user");
    
    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    if ($result->num_rows === 0) {
        echo "No users found in the database.\n";
    } else {
        echo "Users:\n";
        echo "ID | Username | Created At\n";
        echo "-------------------------\n";
        while ($row = $result->fetch_assoc()) {
            echo $row['id'] . " | " . $row['username'] . " | " . $row['created_at'] . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) $conn->close();
}
?> 