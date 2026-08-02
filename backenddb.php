<?php
$conn = new mysqli("localhost", "movieuser", "1234", "cinesphere_db");

if ($conn->connect_error) {
    die("DB FAILED: " . $conn->connect_error);
} else {
    echo "DB CONNECTED SUCCESSFULLY";
}
?>
