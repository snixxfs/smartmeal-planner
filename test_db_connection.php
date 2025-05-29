<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Attempting to connect to the database...\n";

try {
    require_once 'db.php';
    
    if ($conn) {
        echo "Database connection successful!\n";
    } else {
        echo "Database connection failed.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) $conn->close();
}

echo "Connection test complete.\n";
?> 