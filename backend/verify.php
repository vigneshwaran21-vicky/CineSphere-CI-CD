<?php
require_once 'db.php';

$token = isset($_GET['token']) ? $_GET['token'] : '';

if (empty($token)) {
    die("Invalid request. No token provided.");
}

try {
    // Search for the user with this verification token
    $query = "SELECT id FROM users WHERE verification_token = :token AND is_verified = 0";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $userId = $user['id'];

        // Update the user to verified
        $update_query = "UPDATE users SET is_verified = 1, verification_token = NULL WHERE id = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':id', $userId);
        
        if ($update_stmt->execute()) {
            echo "
            <!DOCTYPE html>
            <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>Account Verified | CineSphere</title>
                <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap' rel='stylesheet'>
                <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css'>
                <style>
                    :root {
                        --primary: #f5c518;
                        --bg: #0a0a0a;
                    }
                    body { 
                        font-family: 'Inter', sans-serif; 
                        background: var(--bg); 
                        color: #fff; 
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                        margin: 0;
                        overflow: hidden;
                    }
                    .glass-card {
                        background: rgba(255, 255, 255, 0.03);
                        backdrop-filter: blur(20px);
                        border: 1px solid rgba(255, 255, 255, 0.1);
                        border-radius: 24px;
                        padding: 3rem;
                        text-align: center;
                        max-width: 450px;
                        width: 90%;
                        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
                        animation: slideUp 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
                    }
                    @keyframes slideUp {
                        from { opacity: 0; transform: translateY(40px); }
                        to { opacity: 1; transform: translateY(0); }
                    }
                    .icon-circle {
                        width: 80px;
                        height: 80px;
                        background: var(--primary);
                        color: #000;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 2.5rem;
                        margin: 0 auto 2rem;
                        box-shadow: 0 0 30px rgba(245, 197, 24, 0.4);
                        animation: scaleIn 0.5s 0.3s both cubic-bezier(0.175, 0.885, 0.32, 1.275);
                    }
                    @keyframes scaleIn {
                        from { opacity: 0; transform: scale(0.5); }
                        to { opacity: 1; transform: scale(1); }
                    }
                    h1 { 
                        font-size: 2.2rem; 
                        font-weight: 800; 
                        margin-bottom: 1rem;
                        background: linear-gradient(to bottom, #fff, #aaa);
                        -webkit-background-clip: text;
                        -webkit-text-fill-color: transparent;
                    }
                    p { color: #888; line-height: 1.6; margin-bottom: 2.5rem; }
                    .btn { 
                        background: var(--primary); 
                        color: #000; 
                        padding: 1rem 2.5rem; 
                        border-radius: 12px; 
                        text-decoration: none; 
                        font-weight: 700; 
                        display: inline-block; 
                        transition: all 0.3s;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                        font-size: 0.9rem;
                    }
                    .btn:hover { 
                        transform: translateY(-3px); 
                        box-shadow: 0 10px 20px rgba(245, 197, 24, 0.3);
                    }
                </style>
            </head>
            <body>
                <div class='glass-card'>
                    <div class='icon-circle'><i class='fas fa-check'></i></div>
                    <h1>Verified!</h1>
                    <p>Welcome to the CineSphere family. Your account is now fully active and ready for your first review.</p>
                    <a href='../frontend/login.html' class='btn'>Start Exploring</a>
                </div>
            </body>
            </html>";
        } else {
            echo "Verification failed. Please contact support.";
        }
    } else {
        echo "Invalid or expired verification link.";
    }
} catch (Exception $e) {
    echo "Server error: " . $e->getMessage();
}
?>
