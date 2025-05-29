<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';
    
    // Array of test users
    $test_users = [
        ['username' => 'john.doe@example.com', 'password' => 'john123'],
        ['username' => 'jane.smith@example.com', 'password' => 'jane123'],
        ['username' => 'bob.wilson@example.com', 'password' => 'bob123'],
        ['username' => 'alice.jones@example.com', 'password' => 'alice123']
    ];
    
    $stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
    
    foreach ($test_users as $user) {
        // Hash the password
        $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
        
        // Try to insert the user
        $stmt->bind_param("ss", $user['username'], $hashed_password);
        
        if ($stmt->execute()) {
            echo "✅ Created user: " . $user['username'] . "<br>";
            
            // Add some test meals for this user
            $user_id = $conn->insert_id;
            $meal_stmt = $conn->prepare("
                INSERT INTO meals (user_id, name, ingredients, instructions, meal_date) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            // Add a test meal
            $meal_name = "Test Meal for " . $user['username'];
            $ingredients = "Ingredient 1, Ingredient 2, Ingredient 3";
            $instructions = "Step 1: Do this\nStep 2: Do that";
            $meal_date = date('Y-m-d');
            
            $meal_stmt->bind_param("issss", $user_id, $meal_name, $ingredients, $instructions, $meal_date);
            
            if ($meal_stmt->execute()) {
                echo "  ✅ Added test meal for user<br>";
            }
            
            // Add a test shopping item
            $shopping_stmt = $conn->prepare("
                INSERT INTO shopping_list (user_id, item_name) 
                VALUES (?, ?)
            ");
            
            $item_name = "Test Item for " . $user['username'];
            $shopping_stmt->bind_param("is", $user_id, $item_name);
            
            if ($shopping_stmt->execute()) {
                echo "  ✅ Added test shopping item for user<br>";
            }
            
        } else {
            echo "❌ Failed to create user: " . $user['username'] . " - " . $stmt->error . "<br>";
        }
    }
    
    echo "<br>✅ Test users setup completed!";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?> 