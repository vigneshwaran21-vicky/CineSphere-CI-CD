 	<?php
session_start();
require_once 'db.php';

header("Content-Type: application/json");

// 🔐 ADMIN LOGIN DETAILS
$ADMIN_USER = "admin";
$ADMIN_PASS = "admin123";

$action = $_GET['action'] ?? '';

// ✅ LOGIN
if ($action === 'login') {
    $data = json_decode(file_get_contents("php://input"));

    if ($data->username === $ADMIN_USER && $data->password === $ADMIN_PASS) {
        $_SESSION['admin'] = true;
        echo json_encode(["message" => "Login successful"]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "Invalid credentials"]);
    }
    exit;
}

// 🔒 CHECK LOGIN
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    echo json_encode(["message" => "Unauthorized"]);
    exit;
}

// ✅ GET USERS
if ($action === 'users') {
    $stmt = $conn->query("SELECT id, name, email, is_verified FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

// ✅ DELETE ALL USERS
elseif ($action === 'clear') {
    $conn->exec("TRUNCATE TABLE users");
    echo json_encode(["message" => "All users deleted"]);
}

// ✅ DELETE SINGLE USER
elseif ($action === 'delete') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
    echo json_encode(["message" => "User deleted"]);
}

else {
    echo json_encode(["message" => "Invalid action"]);
}
?>
