<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="users_export.csv"');
header('Access-Control-Allow-Origin: *');

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once 'db.php';

    // Fetch user data
    $result = $conn->query("SELECT id, username, created_at FROM user ORDER BY created_at DESC");

    if (!$result) {
        throw new Exception("Database error: " . $conn->error);
    }

    $output = fopen('php://output', 'w');

    // Add CSV headers
    fputcsv($output, array('ID', 'Username', 'Created At'));

    // Add user data to CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }

    fclose($output);

} catch (Exception $e) {
    // In case of error, output a simple message or log it
    // Avoid outputting sensitive error details in a production environment
    echo "Error generating CSV: " . $e->getMessage();
} finally {
    if (isset($conn)) $conn->close();
}
?> 