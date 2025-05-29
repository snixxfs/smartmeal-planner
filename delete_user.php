<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Max-Age: 86400");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';

    // Get JSON input
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Validate input (check if userId is set and is an integer)
    if (!isset($data['userId']) || !filter_var($data['userId'], FILTER_VALIDATE_INT)) {
        throw new Exception("Invalid User ID provided");
    }

    $userId = $data['userId'];

    // Prepare statement to delete user
    // Using transactions to ensure data integrity (delete related data first)
    $conn->begin_transaction();

    // Delete related data (meals and shopping list items) first to avoid foreign key constraints
    $stmt_meals = $conn->prepare("DELETE FROM meals WHERE user_id = ?");
    if (!$stmt_meals) throw new Exception("Prepare failed (meals): " . $conn->error);
    $stmt_meals->bind_param("i", $userId);
    if (!$stmt_meals->execute()) throw new Exception("Execute failed (meals): " . $stmt_meals->error);

    $stmt_shopping = $conn->prepare("DELETE FROM shopping_list WHERE user_id = ?");
     if (!$stmt_shopping) throw new Exception("Prepare failed (shopping list): " . $conn->error);
    $stmt_shopping->bind_param("i", $userId);
    if (!$stmt_shopping->execute()) throw new Exception("Execute failed (shopping list): " . $stmt_shopping->error);

    // Now delete the user
    $stmt_user = $conn->prepare("DELETE FROM user WHERE id = ?");
    if (!$stmt_user) throw new Exception("Prepare failed (user): " . $conn->error);
    $stmt_user->bind_param("i", $userId);

    if (!$stmt_user->execute()) {
        throw new Exception("Error deleting user: " . $stmt_user->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        "success" => true,
        "message" => "User deleted successfully",
        "userId" => $userId
    ]);

} catch (Exception $e) {
    // Rollback transaction in case of error
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt_meals)) $stmt_meals->close();
    if (isset($stmt_shopping)) $stmt_shopping->close();
    if (isset($stmt_user)) $stmt_user->close();
    if (isset($conn)) $conn->close();
}
?> 