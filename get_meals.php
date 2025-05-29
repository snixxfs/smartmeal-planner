<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

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
    $stmt = $conn->prepare("SELECT * FROM meals WHERE user_id = ? ORDER BY meal_date DESC");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param("i", $data['user_id']);

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Get result
    $result = $stmt->get_result();
    $meals = [];

    // Fetch all meals
    while ($row = $result->fetch_assoc()) {
        // Format the date
        $date = new DateTime($row['meal_date']);
        $row['date'] = $date->format('F j, Y');
        
        $meals[] = $row;
    }

    echo json_encode([
        'success' => true,
        'meals' => $meals
    ]);

} catch (Exception $e) {
    error_log("Error in get_meals.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading meals: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?>
