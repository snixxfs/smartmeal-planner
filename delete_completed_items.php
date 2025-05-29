<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if user_id is present
if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $conn->prepare("DELETE FROM shopping_list WHERE user_id = ? AND completed = 1");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $data['user_id']);

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Completed items cleared successfully',
        'affected_rows' => $stmt->affected_rows
    ]);

} catch (Exception $e) {
    error_log("Error in delete_completed_items.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error clearing completed items: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>

