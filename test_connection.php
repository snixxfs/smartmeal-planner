<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Connection parameters
$host = '127.0.0.1';
$user = 'root';
$password = '';
$port = 3306;

echo "Testing MySQL Connection...\n\n";

// Test 1: Check if MySQL module is loaded
echo "Test 1: Checking MySQL module\n";
if (extension_loaded('mysqli')) {
    echo "✓ MySQL module is loaded\n";
} else {
    echo "✗ MySQL module is not loaded\n";
}

// Test 2: Check if port is accessible
echo "\nTest 2: Checking port accessibility\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 5);
if ($socket) {
    echo "✓ Port $port is accessible\n";
    fclose($socket);
} else {
    echo "✗ Port $port is not accessible: $errstr ($errno)\n";
}

// Test 3: Attempt MySQL connection
echo "\nTest 3: Testing MySQL connection\n";
try {
    $mysqli = mysqli_init();
    
    if (!$mysqli) {
        throw new Exception("mysqli_init failed");
    }
    
    $mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
    
    if (!$mysqli->real_connect($host, $user, $password, '', $port)) {
        throw new Exception($mysqli->connect_error . " (Error #" . $mysqli->connect_errno . ")");
    }
    
    echo "✓ Successfully connected to MySQL\n";
    echo "Server info: " . $mysqli->server_info . "\n";
    echo "Server version: " . $mysqli->server_version . "\n";
    
    $mysqli->close();
    
} catch (Exception $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
}

// Test 4: Check MySQL data directory permissions
echo "\nTest 4: Checking MySQL data directory permissions\n";
$dataDir = "C:/xampp/mysql/data";
if (file_exists($dataDir)) {
    echo "Data directory exists\n";
    if (is_readable($dataDir)) {
        echo "✓ Directory is readable\n";
    } else {
        echo "✗ Directory is not readable\n";
    }
    if (is_writable($dataDir)) {
        echo "✓ Directory is writable\n";
    } else {
        echo "✗ Directory is not writable\n";
    }
} else {
    echo "✗ Data directory not found\n";
} 