<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db.php';

try {
    // Get JSON data from request
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('No data received');
    }

    // Validate required fields
    if (empty($data['user_id']) || empty($data['item_name'])) {
        throw new Exception('Missing required fields');
    }

    // Prepare SQL statement
    $sql = "INSERT INTO shopping_list (user_id, item_name, completed) VALUES (?, ?, 0)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("is", 
        $data['user_id'],
        $data['item_name']
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception('Failed to save shopping item: ' . $stmt->error);
    }

    // Get the ID of the newly inserted item
    $item_id = $conn->insert_id;

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Shopping item saved successfully',
        'item_id' => $item_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
