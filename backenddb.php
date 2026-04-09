<?php
$conn = new mysqli("localhost", "movieuser", "1234", "moviedb");

if ($conn->connect_error) {
    die("DB FAILED: " . $conn->connect_error);
} else {
    echo "DB CONNECTED SUCCESSFULLY";
}
?>
