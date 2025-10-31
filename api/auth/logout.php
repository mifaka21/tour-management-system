<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    session_unset();
    session_destroy();
}

echo json_encode(["success" => true, "message" => "Logged out successfully."]);
?>