<?php
session_start();
header('Content-Type: application/json');
include '../../db/connect.php';

// Ensure user is logged in and is a guide
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guide' && $_SESSION['role'] !== 'admin')) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Only guides or admins can update tours."]);
    exit();
}

// Get raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$tour_id = (int)($data['id'] ?? 0);
$title = trim($data['title'] ?? '');
$description = trim($data['description'] ?? '');
$location = trim($data['location'] ?? '');
$duration = trim($data['duration'] ?? '');
$price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);

if (!$tour_id || !$title || !$location || $price === false || $price <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid or missing data. Title, location, and valid price are required."]);
    exit();
}

// Verify the tour exists and belongs to this guide (unless admin)
$stmt = $pdo->prepare("
    SELECT t.guide_id, u.role 
    FROM tours t 
    JOIN users u ON t.guide_id = u.id 
    WHERE t.id = ?
");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();

if (!$tour) {
    echo json_encode(["success" => false, "message" => "Tour not found."]);
    exit();
}

// Allow update only if user owns the tour or is admin
if ($_SESSION['role'] !== 'admin' && $tour['guide_id'] != $_SESSION['user_id']) {
    echo json_encode(["success" => false, "message" => "You can only update your own tours."]);
    exit();
}

// Perform update
$stmt = $pdo->prepare("
    UPDATE tours 
    SET title = ?, description = ?, location = ?, duration = ?, price = ?
    WHERE id = ?
");
$updated = $stmt->execute([$title, $description, $location, $duration, $price, $tour_id]);

if ($updated) {
    echo json_encode(["success" => true, "message" => "Tour updated successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update tour."]);
}
?>