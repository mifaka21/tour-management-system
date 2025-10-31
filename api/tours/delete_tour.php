<?php
session_start();
header('Content-Type: application/json');
include '../../db/connect.php';

// Ensure user is logged in and is a guide or admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guide' && $_SESSION['role'] !== 'admin')) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Only guides or admins can delete tours."]);
    exit();
}

// Get tour ID from JSON body
$data = json_decode(file_get_contents('php://input'), true);
$tour_id = (int)($data['id'] ?? 0);

if (!$tour_id) {
    echo json_encode(["success" => false, "message" => "Tour ID is required."]);
    exit();
}

// Verify ownership (or admin)
$stmt = $pdo->prepare("SELECT guide_id FROM tours WHERE id = ?");
$stmt->execute([$tour_id]);
$tour = $stmt->fetch();

if (!$tour) {
    echo json_encode(["success" => false, "message" => "Tour not found."]);
    exit();
}

if ($_SESSION['role'] !== 'admin' && $tour['guide_id'] != $_SESSION['user_id']) {
    echo json_encode(["success" => false, "message" => "You can only delete your own tours."]);
    exit();
}

// Delete tour (bookings auto-deleted via CASCADE)
$stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
$deleted = $stmt->execute([$tour_id]);

if ($deleted) {
    echo json_encode(["success" => true, "message" => "Tour deleted successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete tour."]);
}
?>