<?php
header('Content-Type: application/json');
include '../../db/connect.php';

$stmt = $pdo->query("
    SELECT t.*, u.name AS guide_name 
    FROM tours t 
    JOIN users u ON t.guide_id = u.id
    ORDER BY t.created_at DESC
");

$tours = $stmt->fetchAll();
echo json_encode(["success" => true, "tours" => $tours]);
?>