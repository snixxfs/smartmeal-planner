<?php
header('Content-Type: application/json');
require_once 'db.php';

try {
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Validate required fields
    if (!isset($data['id']) || !isset($data['user_id'])) {
        throw new Exception('Missing required fields');
    }

    $item_id = $data['id'];
    $user_id = $data['user_id'];

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM shopping_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $item_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete item');
    }

    // Check if any row was affected
    if ($stmt->affected_rows === 0) {
        throw new Exception('Item not found or already deleted');
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Item deleted successfully'
    ]);

} catch (Exception $e) {
    // Return error response
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close the statement and connection
if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 