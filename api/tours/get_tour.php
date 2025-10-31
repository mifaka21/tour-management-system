<?php
header('Content-Type: application/json');
include '../../db/connect.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Valid tour ID is required."]);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            t.id, t.title, t.description, t.location, t.duration, t.price,
            u.name AS guide_name, u.email AS guide_email
        FROM tours t
        JOIN users u ON t.guide_id = u.id
        WHERE t.id = ?
    ");
    $stmt->execute([$id]);
    $tour = $stmt->fetch();

    if (!$tour) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Tour not found."]);
    } else {
        echo json_encode(["success" => true, "tour" => $tour]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server error."]);
}
?>