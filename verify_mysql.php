<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MySQL Installation Verification</h2>";

try {
    echo "Step 1: Connecting to MySQL...<br>";
    $conn = new mysqli('localhost', 'root', '');
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    echo "✅ Connected successfully!<br>";
    echo "MySQL version: " . $conn->server_info . "<br>";
    
    echo "<br>Step 2: Creating database...<br>";
    $dbname = "smartmealplan";
    if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
        echo "✅ Database created or already exists<br>";
    } else {
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    echo "<br>Step 3: Selecting database...<br>";
    if ($conn->select_db($dbname)) {
        echo "✅ Database selected successfully<br>";
    } else {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    echo "<br>Step 4: Creating tables...<br>";
    $tables = [
        "user" => "CREATE TABLE IF NOT EXISTS user (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "meals" => "CREATE TABLE IF NOT EXISTS meals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            ingredients TEXT,
            instructions TEXT,
            meal_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        )",
        "shopping_list" => "CREATE TABLE IF NOT EXISTS shopping_list (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            item_name VARCHAR(100) NOT NULL,
            completed BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $table_name => $sql) {
        if ($conn->query($sql)) {
            echo "✅ Table '$table_name' created successfully<br>";
        } else {
            throw new Exception("Error creating table $table_name: " . $conn->error);
        }
    }
    
    echo "<br>Step 5: Testing table insertion...<br>";
    $test_user = "test_user_" . time();
    $test_pass = password_hash("test123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $test_user, $test_pass);
    
    if ($stmt->execute()) {
        echo "✅ Test user created successfully<br>";
        $user_id = $stmt->insert_id;
        
        // Clean up test user
        $stmt = $conn->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        echo "✅ Test user cleaned up<br>";
    } else {
        throw new Exception("Error creating test user: " . $stmt->error);
    }
    
    echo "<br>✅ All tests passed! MySQL is working correctly!<br>";
    
} catch (Exception $e) {
    echo "<br>❌ ERROR: " . $e->getMessage() . "<br>";
    if (isset($conn)) {
        echo "MySQL Error #: " . $conn->errno . "<br>";
        echo "MySQL Error: " . $conn->error . "<br>";
    }
}

if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?> 