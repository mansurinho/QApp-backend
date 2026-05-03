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

$university_id = intval($_GET['id'] ?? 0);

if (!$university_id) {
    echo json_encode(["success" => false, "message" => "University id is required"]);
    exit();
}

// Get university
$stmt = mysqli_prepare($conn, "SELECT * FROM universities WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $university_id);
mysqli_stmt_execute($stmt);
$result     = mysqli_stmt_get_result($stmt);
$university = mysqli_fetch_assoc($result);

if (!$university) {
    echo json_encode(["success" => false, "message" => "University not found"]);
    exit();
}

// Get programs
$prog_stmt = mysqli_prepare($conn, "SELECT * FROM programs WHERE university_id = ? ORDER BY name ASC");
mysqli_stmt_bind_param($prog_stmt, "i", $university_id);
mysqli_stmt_execute($prog_stmt);
$prog_result = mysqli_stmt_get_result($prog_stmt);
$programs    = [];
while ($row = mysqli_fetch_assoc($prog_result)) {
    $programs[] = $row;
}
$university['programs'] = $programs;

// Get scholarships
$sch_stmt = mysqli_prepare($conn, "SELECT * FROM scholarships WHERE university_id = ?");
mysqli_stmt_bind_param($sch_stmt, "i", $university_id);
mysqli_stmt_execute($sch_stmt);
$sch_result  = mysqli_stmt_get_result($sch_stmt);
$scholarships = [];
while ($row = mysqli_fetch_assoc($sch_result)) {
    $scholarships[] = $row;
}
$university['scholarships'] = $scholarships;

// Get deadlines
$dead_stmt = mysqli_prepare($conn, "SELECT * FROM deadlines WHERE university_id = ? ORDER BY date ASC");
mysqli_stmt_bind_param($dead_stmt, "i", $university_id);
mysqli_stmt_execute($dead_stmt);
$dead_result = mysqli_stmt_get_result($dead_stmt);
$deadlines   = [];
while ($row = mysqli_fetch_assoc($dead_result)) {
    $deadlines[] = $row;
}
$university['deadlines'] = $deadlines;

echo json_encode(["success" => true, "university" => $university]);

mysqli_close($conn);
?>
