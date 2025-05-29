<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Check if admin table exists
    $result = $conn->query("SHOW TABLES LIKE 'admin'");
    if ($result->num_rows === 0) {
        echo "Admin table does not exist!";
        exit;
    }
    
    // Get admin users
    $result = $conn->query("SELECT id, username, password FROM admin");
    if (!$result) {
        throw new Exception("Error querying admin table: " . $conn->error);
    }
    
    echo "<h2>Admin Users:</h2>";
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
} finally {
    if (isset($conn)) $conn->close();
}
?> 