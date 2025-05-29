<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400"); // 24 hours

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Get JSON input
    $input = file_get_contents("php://input");
    if (!$input) {
        throw new Exception("No input received");
    }
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON format");
    }

    // Validate input
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception("Username and password are required");
    }

    $username = $data['username'];
    $password = $data['password'];

    // Prepare statement to prevent SQL injection - now using admin table
    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Invalid admin credentials");
    }

    $admin = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $admin['password'])) {
        throw new Exception("Invalid admin credentials");
    }

    // Return success with admin data (excluding password)
    unset($admin['password']);
    echo json_encode([
        "success" => true,
        "message" => "Admin login successful",
        "id" => $admin['id'],
        "username" => $admin['username'],
        "isAdmin" => true
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?> 