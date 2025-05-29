<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";

try {
    $conn = new mysqli($servername, $username, $password);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error . " (Error #" . $conn->connect_errno . ")");
    }
    
    echo "Connected successfully to MySQL!<br>";
    echo "MySQL version: " . $conn->server_info . "<br>";
    echo "Current character set: " . $conn->character_set_name() . "<br>";
    
    $result = $conn->query("SHOW DATABASES");
    if ($result) {
        echo "<br>Available databases:<br>";
        while ($row = $result->fetch_array()) {
            echo $row[0] . "<br>";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 