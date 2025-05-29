<?php
header('Content-Type: application/json');
require_once 'db.php';

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (!isset($data['user_id']) || !isset($data['name']) || !isset($data['ingredients']) || !isset($data['instructions']) || !isset($data['meal_date'])) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

try {
    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO meals (user_id, name, ingredients, instructions, meal_date) VALUES (?, ?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    // Convert user_id to integer
    $user_id = (int)$data['user_id'];
    
    // Bind parameters
    $stmt->bind_param("issss", 
        $user_id,
        $data['name'],
        $data['ingredients'],
        $data['instructions'],
        $data['meal_date']
    );

    // Execute statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    // Get the inserted meal ID
    $meal_id = $stmt->insert_id;

    // Fetch the newly created meal
    $select_stmt = $conn->prepare("SELECT * FROM meals WHERE id = ?");
    $select_stmt->bind_param("i", $meal_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $meal = $result->fetch_assoc();

    // Format the date for the response
    $date = new DateTime($meal['meal_date']);
    $meal['date'] = $date->format('F j, Y');

    echo json_encode([
        'success' => true,
        'message' => 'Meal saved successfully',
        'meal' => $meal
    ]);

} catch (Exception $e) {
    error_log("Error in save_meal.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error saving meal: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($select_stmt)) {
        $select_stmt->close();
    }
    $conn->close();
}
?>
