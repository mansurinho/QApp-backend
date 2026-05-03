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

// -----------------------------------------------
// PUT YOUR OPENAI API KEY HERE
// -----------------------------------------------
$OPENAI_API_KEY = "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";
// -----------------------------------------------

$data          = json_decode(file_get_contents("php://input"), true);
$student_id    = intval($data['user_id'] ?? 0);
$university_id = intval($data['university_id'] ?? 0);
$question      = trim($data['question'] ?? '');

if (!$question) {
    echo json_encode(["success" => false, "message" => "Question is required"]);
    exit();
}

// Get student if provided
$student_context = "";
if ($student_id) {
    $stmt = mysqli_prepare($conn, "SELECT name, gpa, ielts, sat, interests, preferred_language, needs_scholarship, preferred_cities FROM students WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $student_id);
    mysqli_stmt_execute($stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    if ($student) {
        $student_context = "Student: {$student['name']}, GPA: {$student['gpa']}, IELTS: {$student['ielts']}, SAT: {$student['sat']}, Interests: {$student['interests']}, Needs scholarship: " . ($student['needs_scholarship'] ? 'Yes' : 'No');
    }
}

// Get university if provided
$university_context = "";
if ($university_id) {
    $stmt2 = mysqli_prepare($conn, "SELECT name, city, languages, min_gpa, min_ielts FROM universities WHERE id = ?");
    mysqli_stmt_bind_param($stmt2, "i", $university_id);
    mysqli_stmt_execute($stmt2);
    $university = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    if ($university) {
        $university_context = "University: {$university['name']}, City: {$university['city']}, Languages: {$university['languages']}, Min GPA: {$university['min_gpa']}, Min IELTS: {$university['min_ielts']}";
    }
}

$system_prompt = "You are a helpful university admissions advisor for QApp, a platform for applying to Kazakh universities. Be concise, friendly, and helpful. Answer in the same language the student uses.";

if ($student_context || $university_context) {
    $system_prompt .= "\n\nContext:\n$student_context\n$university_context";
}

$payload = json_encode([
    "model"    => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user",   "content" => $question]
    ],
    "max_tokens"  => 500,
    "temperature" => 0.7
]);

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer $OPENAI_API_KEY"
]);

$response    = curl_exec($ch);
$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_status !== 200) {
    echo json_encode(["success" => false, "message" => "AI service error"]);
    exit();
}

$ai_data = json_decode($response, true);
$answer  = $ai_data['choices'][0]['message']['content'] ?? 'Sorry, I could not generate a response.';

echo json_encode([
    "success" => true,
    "answer"  => $answer
]);

mysqli_close($conn);
?>
