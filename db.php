<?php
// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error handling to capture all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'mysql_errors.log');

// Ensure JSON output for all responses
header('Content-Type: application/json');

// Database configuration
$servername = "localhost";  // Changed from 127.0.0.1 to localhost
$username = "root";  // Make sure this is exactly "root"
$password = "";      // Empty password for XAMPP default
$port = 3306;
$dbname = "smartmealplan";

try {
    // Create connection without database selection first
    $conn = new mysqli($servername, $username, $password, "", $port);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("MySQL Connection Error: " . $conn->connect_error);
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname";
    if (!$conn->query($sql)) {
        error_log("Error creating database: " . $conn->error);
        throw new Exception("Error creating database: " . $conn->error);
    }
    
    // Select the database
    if (!$conn->select_db($dbname)) {
        error_log("Error selecting database: " . $conn->error);
        throw new Exception("Error selecting database: " . $conn->error);
    }
    
    // Create tables if they don't exist
    $tables = [
        "user" => "CREATE TABLE IF NOT EXISTS user (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "meals" => "CREATE TABLE IF NOT EXISTS meals (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            name VARCHAR(100) NOT NULL,
            ingredients TEXT,
            instructions TEXT,
            meal_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        "shopping_list" => "CREATE TABLE IF NOT EXISTS shopping_list (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            item_name VARCHAR(100) NOT NULL,
            completed BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    foreach ($tables as $table_name => $sql) {
        if (!$conn->query($sql)) {
            error_log("Error creating $table_name table: " . $conn->error);
            throw new Exception("Error creating $table_name table: " . $conn->error);
        }
    }
    
    // Set character set
    if (!$conn->set_charset("utf8mb4")) {
        error_log("Error setting charset: " . $conn->error);
        throw new Exception("Error setting charset: " . $conn->error);
    }
    
} catch (Exception $e) {
    error_log("Database Error: " . $e->getMessage());
    
    // Only try to close the connection if it was successfully established
    if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
        $conn->close();
    }
    
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection error: " . $e->getMessage(),
        "debug_info" => [
            "server" => $servername,
            "port" => $port,
            "database" => $dbname
        ]
    ]);
    exit();
}

// Function to prevent SQL injection
function sanitize_input($conn, $value) {
    if (is_array($value)) {
        return array_map(function($item) use ($conn) {
            return sanitize_input($conn, $item);
        }, $value);
    }
    return $conn->real_escape_string($value);
}
?>
