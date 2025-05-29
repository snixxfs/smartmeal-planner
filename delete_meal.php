<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$meal_id = $data['meal_id'];

$conn->query("DELETE FROM meals WHERE id = $meal_id");

echo json_encode(["success" => true]);
?>
