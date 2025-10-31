<?php
session_start();
header('Content-Type: application/json');
include '../../db/connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guide') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Only guides can create tours."]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$title = $data['title'] ?? '';
$desc = $data['description'] ?? '';
$location = $data['location'] ?? '';
$duration = $data['duration'] ?? '';
$price = $data['price'] ?? 0;

if (!$title || !$location || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Title, location, and valid price required."]);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO tours (title, description, location, duration, price, guide_id) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$title, $desc, $location, $duration, $price, $_SESSION['user_id']]);

echo json_encode(["success" => true, "message" => "Tour created successfully!", "tour_id" => $pdo->lastInsertId()]);
?>