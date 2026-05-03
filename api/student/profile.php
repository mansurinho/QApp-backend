<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../../config/db.php';

// GET — return student profile
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $student_id = intval($_GET['user_id'] ?? 0);

    if (!$student_id) {
        echo json_encode(["success" => false, "message" => "user_id is required"]);
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT id, name, email, avatar, grade, country, city, gpa, gpa_max, ielts, sat, interests, preferred_language, needs_scholarship, preferred_cities, goal FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result  = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if (!$student) {
        echo json_encode(["success" => false, "message" => "Student not found"]);
        exit();
    }

    // Parse comma-separated fields into arrays
    $student['interests']        = $student['interests'] ? explode(',', $student['interests']) : [];
    $student['preferred_cities'] = $student['preferred_cities'] ? explode(',', $student['preferred_cities']) : [];

    // Get documents
    $doc_stmt = mysqli_prepare($conn, "SELECT id, name, status FROM documents WHERE student_id = ?");
    mysqli_stmt_bind_param($doc_stmt, "i", $student_id);
    mysqli_stmt_execute($doc_stmt);
    $doc_result = mysqli_stmt_get_result($doc_stmt);
    $documents  = [];
    while ($row = mysqli_fetch_assoc($doc_result)) {
        $documents[] = $row;
    }
    $student['documents'] = $documents;

    echo json_encode(["success" => true, "student" => $student]);
}

// POST — update student profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data       = json_decode(file_get_contents("php://input"), true);
    $student_id = intval($data['user_id'] ?? 0);

    if (!$student_id) {
        echo json_encode(["success" => false, "message" => "user_id is required"]);
        exit();
    }

    $grade             = intval($data['grade'] ?? 0);
    $country           = trim($data['country'] ?? '');
    $city              = trim($data['city'] ?? '');
    $gpa               = floatval($data['gpa'] ?? 0);
    $ielts             = floatval($data['ielts'] ?? 0);
    $sat               = intval($data['sat'] ?? 0);
    $interests         = is_array($data['interests'] ?? '') ? implode(',', $data['interests']) : trim($data['interests'] ?? '');
    $preferred_language = trim($data['preferred_language'] ?? '');
    $needs_scholarship = intval($data['needs_scholarship'] ?? 0);
    $preferred_cities  = is_array($data['preferred_cities'] ?? '') ? implode(',', $data['preferred_cities']) : trim($data['preferred_cities'] ?? '');
    $goal              = trim($data['goal'] ?? '');

    $stmt = mysqli_prepare($conn, "UPDATE students SET grade=?, country=?, city=?, gpa=?, ielts=?, sat=?, interests=?, preferred_language=?, needs_scholarship=?, preferred_cities=?, goal=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "issddisisssi", $grade, $country, $city, $gpa, $ielts, $sat, $interests, $preferred_language, $needs_scholarship, $preferred_cities, $goal, $student_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Profile updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }
}

mysqli_close($conn);
?>
