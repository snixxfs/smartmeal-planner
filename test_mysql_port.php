<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MySQL Port Test</h2>";

$host = '127.0.0.1';
$port = 3306;

echo "Testing connection to MySQL on $host:$port...<br>";

$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if ($socket) {
    echo "✅ Success! MySQL port $port is accessible<br>";
    fclose($socket);
    
    // Try MySQL connection
    echo "Testing MySQL connection...<br>";
    try {
        $conn = new mysqli($host, 'root', '');
        if ($conn->connect_error) {
            echo "❌ MySQL Connection Error: " . $conn->connect_error . "<br>";
            echo "Error Code: " . $conn->connect_errno . "<br>";
        } else {
            echo "✅ Successfully connected to MySQL!<br>";
            echo "MySQL Version: " . $conn->server_info . "<br>";
            $conn->close();
        }
    } catch (Exception $e) {
        echo "❌ MySQL Exception: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ Error: Could not connect to port $port<br>";
    echo "Error $errno: $errstr<br>";
}
?> 