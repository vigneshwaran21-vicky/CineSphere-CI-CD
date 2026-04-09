<?php
// Simple API test file

header("Content-Type: application/json");

// Database connection
$host = "localhost";
$user = "root";        // or movieuser (AWS)
$pass = "";            // or password123
$db   = "cinesphere_db";

// Connect
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed",
        "error" => $conn->connect_error
    ]);
    exit();
}

// Success response
echo json_encode([
    "status" => "success",
    "message" => "Database connected successfully 🚀"
]);

$conn->close();
?>
