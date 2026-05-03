<?php
header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "message" => "QApp API is running",
    "version" => "1.0"
]);
?>
