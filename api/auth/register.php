<?php
header('Content-Type: application/json');
include '../../db/connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$name = trim($data['name'] ?? '');
$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';
$role = $data['role'] ?? 'customer'; // 'guide' or 'customer'
$phone = $data['phone'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email."]);
    exit();
}

if (!in_array($role, ['customer', 'guide'])) {
    $role = 'customer';
}

// Check email exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(["success" => false, "message" => "Email already registered."]);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, $hashedPassword, $role, $phone]);

echo json_encode(["success" => true, "message" => "Registration successful!"]);
?>