<?php
// Disable HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set up error handling to capture all errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

try {
    require_once 'db.php';
    
    // Get JSON input
    $input = file_get_contents("php://input");
    if (!$input) {
        error_log("Login attempt: No input received");
        throw new Exception("No input received");
    }
    
    error_log("Login attempt - Raw input received: " . $input);
    
    $data = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Login attempt - JSON decode error: " . json_last_error_msg());
        throw new Exception("Invalid JSON format: " . json_last_error_msg());
    }

    error_log("Login attempt - Decoded data: " . print_r($data, true));

    // Validate input
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception("Username and password are required");
    }

    $username = sanitize_input($conn, $data['username']);
    $password = $data['password'];

    // Prepare statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, username, password FROM user WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Database error: " . $stmt->error);
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        error_log("Login failed: User not found - " . $username);
        http_response_code(401);
        throw new Exception("Invalid username or password");
    }

    $user = $result->fetch_assoc();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        error_log("Login failed: Invalid password for user - " . $username);
        http_response_code(401);
        throw new Exception("Invalid username or password");
    }

    // Return success with user data (excluding password)
    unset($user['password']);
    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => $user
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    
    // Only set 500 status code for server errors, not authentication failures
    if (strpos($e->getMessage(), "Invalid username or password") === false) {
        http_response_code(500);
    }
    
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    // Close connections
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
