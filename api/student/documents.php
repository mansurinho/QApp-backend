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

// GET — return all documents for a student
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $student_id = intval($_GET['user_id'] ?? 0);

    if (!$student_id) {
        echo json_encode(["success" => false, "message" => "user_id is required"]);
        exit();
    }

    $stmt = mysqli_prepare($conn, "SELECT id, name, status FROM documents WHERE student_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $result    = mysqli_stmt_get_result($stmt);
    $documents = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $documents[] = $row;
    }

    // If student has no documents yet, return default list
    if (empty($documents)) {
        $defaults = [
            "ID / Passport",
            "Photo 3x4",
            "Medical Certificate",
            "Academic Transcript",
            "Diploma / Certificate"
        ];

        foreach ($defaults as $doc_name) {
            $ins = mysqli_prepare($conn, "INSERT INTO documents (student_id, name, status) VALUES (?, ?, 'missing')");
            mysqli_stmt_bind_param($ins, "is", $student_id, $doc_name);
            mysqli_stmt_execute($ins);
            $documents[] = [
                "id"     => mysqli_insert_id($conn),
                "name"   => $doc_name,
                "status" => "missing"
            ];
        }
    }

    echo json_encode(["success" => true, "documents" => $documents]);
}

// POST — update document status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data        = json_decode(file_get_contents("php://input"), true);
    $document_id = intval($data['document_id'] ?? 0);
    $status      = trim($data['status'] ?? '');

    $allowed = ['ready', 'missing', 'pending'];
    if (!in_array($status, $allowed)) {
        echo json_encode(["success" => false, "message" => "Invalid status. Use: ready, missing, pending"]);
        exit();
    }

    $stmt = mysqli_prepare($conn, "UPDATE documents SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "si", $status, $document_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => true, "message" => "Document status updated"]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed"]);
    }
}

mysqli_close($conn);
?>
