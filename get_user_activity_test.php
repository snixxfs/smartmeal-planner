<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    $username = 'testuser@example.com';
    
    echo "Checking activity for user: " . $username . "\n\n";
    
    // Get user ID
    $stmt = $conn->prepare("SELECT id FROM user WHERE username = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed (user): " . $conn->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user = $user_result->fetch_assoc();
    $stmt->close();
    
    if (!$user) {
        echo "User not found!
";
        exit;
    }
    
    $user_id = $user['id'];
    echo "User ID: " . $user_id . "\n\n";
    
    // Get meals for the user
    echo "Meals:\n";
    $stmt = $conn->prepare("SELECT id, name, created_at FROM meals WHERE user_id = ? ORDER BY created_at DESC");
     if (!$stmt) {
        throw new Exception("Prepare failed (meals): " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $meals_result = $stmt->get_result();
    
    if ($meals_result->num_rows === 0) {
        echo "- No meals found\n";
    } else {
        while ($row = $meals_result->fetch_assoc()) {
            print_r($row);
        }
    }
    $stmt->close();
    
    echo "\nShopping List Items:\n";
    // Get shopping list items for the user
    $stmt = $conn->prepare("SELECT id, item_name, created_at FROM shopping_list WHERE user_id = ? ORDER BY created_at DESC");
     if (!$stmt) {
        throw new Exception("Prepare failed (shopping list): " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $shopping_result = $stmt->get_result();
    
    if ($shopping_result->num_rows === 0) {
        echo "- No shopping list items found\n";
    } else {
         while ($row = $shopping_result->fetch_assoc()) {
            print_r($row);
        }
    }
    $stmt->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) $conn->close();
}
?> 