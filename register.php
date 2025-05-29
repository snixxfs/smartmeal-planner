<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log errors to a file
ini_set('log_errors', 1);
ini_set('error_log', 'register_errors.log');

try {
    // Log the start of registration process
    error_log("Starting registration process");
    
    // Include database connection
    require_once 'db.php';
    
    // Get and log raw input
    $raw_input = file_get_contents("php://input");
    error_log("Raw input received: " . $raw_input);
    
    // Decode JSON input
    $data = json_decode($raw_input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Invalid JSON data: " . json_last_error_msg());
    }
    
    // Log decoded data
    error_log("Decoded data: " . print_r($data, true));

    // Validate input
    if (!isset($data['username']) || !isset($data['password'])) {
        throw new Exception("Username and password are required");
    }

    $username = sanitize_input($conn, $data['username']);
    $password = $data['password'];

    // Log sanitized username (never log passwords)
    error_log("Sanitized username: " . $username);

    // Validate username length
    if (strlen($username) < 3 || strlen($username) > 50) {
        throw new Exception("Username must be between 3 and 50 characters");
    }

    // Validate password strength
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long");
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $username);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        throw new Exception("Username already exists");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
    if (!$stmt) {
        throw new Exception("Prepare insert failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $username, $hashed_password);
    
    if (!$stmt->execute()) {
        throw new Exception("Insert failed: " . $stmt->error);
    }
    
    $user_id = $conn->insert_id;
    error_log("User successfully registered with ID: " . $user_id);

    echo json_encode([
        "success" => true,
        "message" => "Registration successful",
        "user_id" => $user_id
    ]);

} catch (Exception $e) {
    error_log("Registration error: " . $e->getMessage());
    echo json_encode([
        "success" => false,
        "message" => "An error occurred during registration",
        "debug_message" => $e->getMessage() // Only include this in development
    ]);
}

// Close connections
if (isset($stmt)) $stmt->close();
if (isset($conn)) $conn->close();
?>
