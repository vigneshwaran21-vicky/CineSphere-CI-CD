<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'db.php';
header("Content-Type: application/json; charset=UTF-8");
ob_start();
require_once 'SmtpMailer.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

// Set to false to use real email sending (requires valid SMTP config below)
define('DEVELOPMENT_MODE', false);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if ($action === 'register') {
        if (!empty($data->name) && !empty($data->email) && !empty($data->password)) {
            try {
                // Check if user exists
                $check_query = "SELECT id FROM users WHERE email = :email";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(":email", $data->email);
                $check_stmt->execute();
                
                if($check_stmt->rowCount() > 0){
                    http_response_code(400);
                    echo json_encode(["message" => "Email already registered."]);
                    exit;
                }

                $v_token = bin2hex(random_bytes(16));
                $query = "INSERT INTO users (name, email, password, verification_token, is_verified) VALUES (:name, :email, :password, :v_token, 0)";
                $stmt = $conn->prepare($query);

                $name = htmlspecialchars(strip_tags($data->name));
                $email = htmlspecialchars(strip_tags($data->email));
                $password = password_hash($data->password, PASSWORD_BCRYPT);

                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":email", $email);
                $stmt->bindParam(":password", $password);
                $stmt->bindParam(":v_token", $v_token);

                if ($stmt->execute()) {
                    require_once 'SmtpMailer.php';
                   $base_url = "http://" . $_SERVER['HTTP_HOST'];
                   $verifyLink = $base_url . "/backend/verify.php?token=" . $v_token;
                    $subject = "Verify Your CineSphere Account";
                    $message = "<h1>Welcome to CineSphere!</h1><p>Please click the link below to verify your account:</p><p><a href='$verifyLink' style='background:#fbc02d; color:#000; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Verify My Account</a></p>";
                    
                    $mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS);
                    $sent = $mailer->send($email, $subject, $message);

                    ob_clean();
                    http_response_code(201);
                    echo json_encode(["message" => $sent ? "Registration successful! Please check your email to verify." : "Registration successful! (Email failed: " . ($mailer->error ?: "Unknown error") . ")"]);
                    exit;
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to register user."]);
                }
            } catch(Exception $e) {
                ob_clean();
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    } elseif ($action === 'login') {
        if (!empty($data->email) && !empty($data->password)) {
            try {
                $query = "SELECT id, name, password, is_verified FROM users WHERE email = :email LIMIT 0,1";
                $stmt = $conn->prepare($query);
                $email = htmlspecialchars(strip_tags($data->email));
                $stmt->bindParam(":email", $email);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($row['is_verified'] == 0) {
                        http_response_code(403);
                        echo json_encode(["message" => "Please verify your email address first."]);
                        exit;
                    }
                    if (password_verify($data->password, $row['password'])) {
                        ob_clean();
                        http_response_code(200);
                        echo json_encode([
                            "message" => "Login successful.",
                            "user" => ["id" => $row['id'], "name" => $row['name'], "email" => $email]
                        ]);
                    } else {
                        http_response_code(401);
                        echo json_encode(["message" => "Invalid credentials."]);
                    }
                } else {
                    ob_clean();
                    http_response_code(401);
                    echo json_encode(["message" => "Account not found."]);
                }
            } catch(Exception $e) {
                ob_clean();
                ob_clean();
                http_response_code(500);
                echo json_encode(["message" => "Server error: " . $e->getMessage()]);
            }
        } else {
            ob_clean();
            http_response_code(400);
            echo json_encode(["message" => "Incomplete login data."]);
        }
    } elseif ($action === 'forgot') {
        if (!empty($data->email)) {
            try {
                $query = "SELECT id, is_verified FROM users WHERE email = :email LIMIT 1";	
                $stmt = $conn->prepare($query);
                $email = htmlspecialchars(strip_tags($data->email));
                $stmt->bindParam(":email", $email);
                $stmt->execute();

                $row = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($row['is_verified'] == 0) {
                http_response_code(403);
                echo json_encode(["message" => "Verify your email before resetting password"]);
                exit;
                }		

                
                if ($stmt->rowCount() > 0) {
                    $token = bin2hex(random_bytes(32));
                    $insert_query = "INSERT INTO password_resets (email, token) VALUES (:email, :token)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bindParam(":email", $email);
                    $insert_stmt->bindParam(":token", $token);
                    
                    if($insert_stmt->execute()){
                        $base_url = "http://" . $_SERVER['HTTP_HOST'];
                        $resetLink = $base_url . "/reset-password.html?token=" . $token;
                        $subject = "CineSphere Password Reset";
                        $message = "<h1>Reset Your Password</h1><p>Click below to reset your password:</p><p><a href='$resetLink' style='background:#fbc02d; color:#000; padding:10px 20px; text-decoration:none; border-radius:5px; font-weight:bold;'>Reset Password</a></p>";
                        
                        $mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS);
                        $sent = $mailer->send($email, $subject, $message);
                        
                        ob_clean();
                        http_response_code(200);
                        echo json_encode(["message" => $sent ? "Reset link sent to your email." : "Account found, but email failed: " . ($mailer->error ?: "Unknown error"), "token" => $token]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "No account found with this email."]);
                }
            } catch(Exception $e) {
                ob_clean();
                http_response_code(500);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Email is required."]);
        }
    } elseif ($action === 'reset_token') {
        if (!empty($data->token) && !empty($data->newPassword)) {
            try {
                // Verify token exists
                $query = "SELECT email FROM password_resets WHERE token = :token ORDER BY created_at DESC LIMIT 0,1";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":token", $data->token);
                $stmt->execute();
                
                if ($stmt->rowCount() > 0) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $email = $row['email'];
// CHECK IF USER VERIFIED
$checkUser = "SELECT is_verified FROM users WHERE email = :email LIMIT 1";
$check_stmt = $conn->prepare($checkUser);
$check_stmt->bindParam(":email", $email);
$check_stmt->execute();
$user = $check_stmt->fetch(PDO::FETCH_ASSOC);

if ($user['is_verified'] == 0) {
    http_response_code(403);
    echo json_encode(["message" => "Verify your email before resetting password"]);
    exit;
}

                    $password_hash = password_hash($data->newPassword, PASSWORD_BCRYPT);
                    $update_query = "UPDATE users SET password = :password WHERE email = :email";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bindParam(":password", $password_hash);
                    $update_stmt->bindParam(":email", $email);
                    
                    if($update_stmt->execute()){
                        // Delete used token
                        $delete_query = "DELETE FROM password_resets WHERE email = :email";
                        $delete_stmt = $conn->prepare($delete_query);
                        $delete_stmt->bindParam(":email", $email);
                        $delete_stmt->execute();

                        ob_clean();
                        http_response_code(200);
                        echo json_encode(["message" => "Password reset successfully!"]);
                    } else {
                        http_response_code(503);
                        echo json_encode(["message" => "Failed to update password."]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["message" => "Invalid or expired token."]);
                }
            } catch(Exception $e) {
                ob_clean();
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    } else {
        ob_clean();
        http_response_code(404);
        echo json_encode(["message" => "Action not found."]);
    }
} else {
    ob_clean();
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed."]);
}
ob_end_flush();
?>
