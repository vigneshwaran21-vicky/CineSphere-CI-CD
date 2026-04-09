<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PUT");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$host = 'localhost';
$db_name = 'cinesphere_db';
$username = 'movieuser';
$password = '1234';

// SYSTEM SENDER EMAIL (The Gmail account that sends the links)
// 1. Go to Google Security -> 2-Step Verification -> App Passwords to get your 16-character code.
// 2. Put your real Gmail address in SMTP_USER.
// 3. Put your 16-character App Password in SMTP_PASS.
define('SMTP_USER', 'official.cinesphere@gmail.com'); 
define('SMTP_PASS', 'bhvj yvjb rqov fqzg'); // 16-character Google App Password
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);

// NOTE: The RECIPIENT email (the user who is registering) is automatically 
// taken from the frontend input field in auth.php and test_mail.php.
try {
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password, [
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch(PDOException $exception) {
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit;
}
?>
