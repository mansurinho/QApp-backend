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
$name     = trim($data['name'] ?? '');
$email    = trim($data['email'] ?? '');
$password = trim($data['password'] ?? '');

if (empty($name) || empty($email) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Name, email and password are required"]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format"]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit();
}

$check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

if (mysqli_stmt_num_rows($check) > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered"]);
    exit();
}

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn, "INSERT INTO students (name, email, password) VALUES (?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);

if (mysqli_stmt_execute($stmt)) {
    $new_id = mysqli_insert_id($conn);
    echo json_encode([
        "success" => true,
        "message" => "Registration successful",
        "user_id" => $new_id,
        "name"    => $name,
        "email"   => $email
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed. Please try again."]);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>
