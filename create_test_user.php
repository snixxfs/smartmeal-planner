<?php
require_once 'db.php';

$username = 'test@example.com';
$password = 'password123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO user (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "Test user created successfully\n";
    echo "Username: " . $username . "\n";
    echo "Password: " . $password . "\n";
} else {
    echo "Error creating test user: " . $stmt->error . "\n";
}

$stmt->close();
$conn->close();
?> 