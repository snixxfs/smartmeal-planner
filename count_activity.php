<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    echo "Checking counts in meals and shopping_list tables:\n\n";
    
    // Count meals
    $meals_count_result = $conn->query("SELECT COUNT(*) as count FROM meals");
    $meals_count = $meals_count_result->fetch_assoc()['count'];
    echo "Meals table count: " . $meals_count . "\n";
    
    // Count shopping list items
    $shopping_count_result = $conn->query("SELECT COUNT(*) as count FROM shopping_list");
    $shopping_count = $shopping_count_result->fetch_assoc()['count'];
    echo "Shopping_list table count: " . $shopping_count . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (isset($conn)) $conn->close();
}
?> 