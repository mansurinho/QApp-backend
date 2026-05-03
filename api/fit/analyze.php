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
$OPENAI_API_KEY = "sk-your-openai-key-here";
// -----------------------------------------------

$data          = json_decode(file_get_contents("php://input"), true);
$student_id    = intval($data['user_id'] ?? 0);
$university_id = intval($data['university_id'] ?? 0);

if (!$student_id || !$university_id) {
    echo json_encode(["success" => false, "message" => "user_id and university_id are required"]);
    exit();
}

// Get student
$stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Get university
$stmt2 = mysqli_prepare($conn, "SELECT * FROM universities WHERE id = ?");
mysqli_stmt_bind_param($stmt2, "i", $university_id);
mysqli_stmt_execute($stmt2);
$university = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));

// Get programs
$stmt3 = mysqli_prepare($conn, "SELECT name, field, language FROM programs WHERE university_id = ?");
mysqli_stmt_bind_param($stmt3, "i", $university_id);
mysqli_stmt_execute($stmt3);
$prog_result = mysqli_stmt_get_result($stmt3);
$programs    = [];
while ($row = mysqli_fetch_assoc($prog_result)) {
    $programs[] = $row['name'] . " (" . $row['field'] . ", " . $row['language'] . ")";
}

// Get scholarships
$stmt4 = mysqli_prepare($conn, "SELECT name, covers, min_gpa, min_ielts FROM scholarships WHERE university_id = ?");
mysqli_stmt_bind_param($stmt4, "i", $university_id);
mysqli_stmt_execute($stmt4);
$sch_result   = mysqli_stmt_get_result($stmt4);
$scholarships = [];
while ($row = mysqli_fetch_assoc($sch_result)) {
    $scholarships[] = $row['name'] . " - " . $row['covers'] . " (min GPA: " . $row['min_gpa'] . ", min IELTS: " . $row['min_ielts'] . ")";
}

// Get documents
$stmt5 = mysqli_prepare($conn, "SELECT name, status FROM documents WHERE student_id = ?");
mysqli_stmt_bind_param($stmt5, "i", $student_id);
mysqli_stmt_execute($stmt5);
$doc_result = mysqli_stmt_get_result($stmt5);
$documents  = [];
while ($row = mysqli_fetch_assoc($doc_result)) {
    $documents[] = $row['name'] . ": " . $row['status'];
}

// Build prompt
$prompt = "
You are an admissions advisor. Analyze if this student is a good fit for this university.

STUDENT PROFILE:
- Name: {$student['name']}
- GPA: {$student['gpa']} / {$student['gpa_max']}
- IELTS: {$student['ielts']}
- SAT: {$student['sat']}
- Interests: {$student['interests']}
- Preferred language: {$student['preferred_language']}
- Needs scholarship: " . ($student['needs_scholarship'] ? 'Yes' : 'No') . "
- Preferred cities: {$student['preferred_cities']}
- Documents: " . implode(', ', $documents) . "

UNIVERSITY:
- Name: {$university['name']}
- City: {$university['city']}
- Type: {$university['type']}
- Languages: {$university['languages']}
- Min GPA required: {$university['min_gpa']}
- Min IELTS required: {$university['min_ielts']}
- Programs: " . implode(', ', $programs) . "
- Scholarships: " . implode(', ', $scholarships) . "

Return ONLY a valid JSON object with no extra text:
{
  \"fit_score\": <number 0-100>,
  \"status\": \"<Strong Match | Good Match | Partial Match | Low Match>\",
  \"fit_reasons\": [\"<reason 1>\", \"<reason 2>\", \"<reason 3>\"],
  \"fit_gaps\": [\"<gap 1>\", \"<gap 2>\"],
  \"next_steps\": [\"<step 1>\", \"<step 2>\", \"<step 3>\"],
  \"scholarship_insight\": \"<one sentence about scholarship chances>\"
}
";

// Call OpenAI
$payload = json_encode([
    "model"    => "gpt-4o-mini",
    "messages" => [
        ["role" => "system", "content" => "You are a university admissions advisor. Always respond with valid JSON only."],
        ["role" => "user",   "content" => $prompt]
    ],
    "max_tokens"  => 800,
    "temperature" => 0.3
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
    echo json_encode(["success" => false, "message" => "AI service error", "details" => $response]);
    exit();
}

$ai_data = json_decode($response, true);
$ai_text = $ai_data['choices'][0]['message']['content'] ?? '';

// Clean and parse JSON from AI response
$ai_text = preg_replace('/```json|```/', '', $ai_text);
$ai_text = trim($ai_text);
$analysis = json_decode($ai_text, true);

if (!$analysis) {
    echo json_encode(["success" => false, "message" => "Failed to parse AI response"]);
    exit();
}

echo json_encode([
    "success"  => true,
    "analysis" => $analysis
]);

mysqli_close($conn);
?>
