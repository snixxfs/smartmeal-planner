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
    $stmt = $conn->prepare("SELECT * FROM shopping_list WHERE user_id = ? ORDER BY created_at DESC");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $data['user_id']);

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Get results
    $result = $stmt->get_result();
    $items = [];
    
    while ($row = $result->fetch_assoc()) {
        $items[] = [
            'id' => $row['id'],
            'item_name' => $row['item_name'],
            'completed' => (bool)$row['completed'],
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'items' => $items
    ]);

} catch (Exception $e) {
    error_log("Error in get_shopping_items.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading shopping items: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
