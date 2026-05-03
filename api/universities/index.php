<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/db.php';

$stmt   = mysqli_prepare($conn, "SELECT id, name, name_ru, city, country, type, languages, total_programs, min_gpa, min_ielts, application_deadline, website, campus_photo, description FROM universities ORDER BY name ASC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$universities = [];
while ($row = mysqli_fetch_assoc($result)) {
    $universities[] = $row;
}

echo json_encode(["success" => true, "universities" => $universities]);

mysqli_close($conn);
?>
