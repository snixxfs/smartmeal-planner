<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $conn = new mysqli("localhost", "root", "");
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Drop the database if it exists and create a new one
    $conn->query("DROP DATABASE IF EXISTS smartmealplan");
    echo "Dropped existing database if it existed<br>";
    
    $sql = "CREATE DATABASE smartmealplan";
    if (!$conn->query($sql)) {
        throw new Exception("Error creating database: " . $conn->error);
    }
    echo "Created new database<br>";
    
    // Select the database
    if (!$conn->select_db("smartmealplan")) {
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Set foreign key checks to 0 to allow dropping tables with dependencies
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop existing tables
    $tables = ['shopping_list', 'meals', 'user', 'admin'];
    foreach ($tables as $table) {
        if ($conn->query("DROP TABLE IF EXISTS `$table`")) {
            echo "Dropped table $table if it existed<br>";
        }
    }
    
    // Reset foreign key checks
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");
    
    // Create admin table first
    $sql = "CREATE TABLE `admin` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating admin table: " . $conn->error);
    }
    echo "✅ Admin table created successfully<br>";
    
    // Create user table
    $sql = "CREATE TABLE `user` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(50) UNIQUE NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating user table: " . $conn->error);
    }
    echo "✅ User table created successfully<br>";
    
    // Create meals table with foreign key
    $sql = "CREATE TABLE `meals` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `name` VARCHAR(100) NOT NULL,
        `ingredients` TEXT,
        `instructions` TEXT,
        `meal_date` DATE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating meals table: " . $conn->error);
    }
    echo "✅ Meals table created successfully<br>";
    
    // Create shopping_list table with foreign key
    $sql = "CREATE TABLE `shopping_list` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `user_id` INT NOT NULL,
        `item_name` VARCHAR(100) NOT NULL,
        `completed` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (!$conn->query($sql)) {
        throw new Exception("Error creating shopping_list table: " . $conn->error);
    }
    echo "✅ Shopping list table created successfully<br>";
    
    // Create a test user
    $username = "test@example.com";
    $password = password_hash("password123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO `user` (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        echo "✅ Test user created successfully<br>";
        echo "Username: " . $username . "<br>";
        echo "Password: password123<br>";
    } else {
        throw new Exception("Error creating test user: " . $stmt->error);
    }
    
    // Create admin user in admin table
    $admin_username = "admin";
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO `admin` (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $admin_username, $admin_password);
    
    if ($stmt->execute()) {
        echo "✅ Admin user created successfully<br>";
        echo "Admin Username: " . $admin_username . "<br>";
        echo "Admin Password: admin123<br>";
    } else {
        throw new Exception("Error creating admin user: " . $stmt->error);
    }
    
    echo "<br>✅ Database setup completed successfully!";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?> 