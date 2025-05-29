<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "smartmealplan";

    // First try to connect to MySQL
    echo "Attempting to connect to MySQL...<br>";
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✅ Connected to MySQL successfully<br>";

    // Try to create database
    echo "Attempting to create database...<br>";
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if ($conn->query($sql)) {
        echo "✅ Database created or already exists<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }

    // Select the database
    echo "Attempting to select database...<br>";
    if ($conn->select_db($dbname)) {
        echo "✅ Database selected successfully<br>";
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }

    // Try to create user table
    echo "Attempting to create user table...<br>";
    $sql = "CREATE TABLE IF NOT EXISTS user (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($sql)) {
        echo "✅ User table created or already exists<br>";
    } else {
        throw new Exception("Error creating user table: " . $conn->error);
    }

    // Test table creation
    echo "Testing table creation with a sample user...<br>";
    $username = "test_user_" . time();
    $password = password_hash("test123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        echo "✅ Test user created successfully<br>";
        // Clean up test user
        $stmt = $conn->prepare("DELETE FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        echo "✅ Test user cleaned up<br>";
    } else {
        throw new Exception("Error creating test user: " . $stmt->error);
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "<br>";
    echo "MySQL Error #: " . $conn->errno . "<br>";
    echo "MySQL Error: " . $conn->error . "<br>";
}

if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?> 