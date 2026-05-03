<?php
$conn = mysqli_connect("localhost", "root", "", "qapp");

if (!$conn) {
    die(json_encode([
        "success" => false,
        "message" => "Connection failed: " . mysqli_connect_error()
    ]));
}
?>
