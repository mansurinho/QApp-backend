<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/db.php';

$data     = json_decode(file_get_contents("php://input"), true);
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Email and password are required"]);
    exit();
}

$stmt = mysqli_prepare($conn, "SELECT id, name, email, password FROM students WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student || !password_verify($password, $student['password'])) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit();
}

echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user_id" => $student['id'],
    "name"    => $student['name'],
    "email"   => $student['email']
]);

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
