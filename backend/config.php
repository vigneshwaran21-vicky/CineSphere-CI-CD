<?php
$servername = "localhost";
$username = "movieuser";
$password = "1234";
$database = "moviedb";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
