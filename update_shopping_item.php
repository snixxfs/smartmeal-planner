<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if required fields are present
if (!isset($data['id']) || !isset($data['user_id']) || !isset($data['completed'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $conn->prepare("UPDATE shopping_list SET completed = ? WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("iii", $data['completed'], $data['id'], $data['user_id']);

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Check if any row was affected
    if ($stmt->affected_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No item found or no changes made']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Item updated successfully']);

} catch (Exception $e) {
    error_log("Error in update_shopping_item.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error updating item: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
