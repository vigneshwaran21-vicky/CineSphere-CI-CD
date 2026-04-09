<?php
require_once 'db.php';
require_once 'SmtpMailer.php';

// This script helps you test your SMTP settings from db.php
header("Content-Type: text/plain");

echo "Checking SMTP Settings from db.php...\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP User: " . SMTP_USER . "\n";
echo "SMTP Pass: " . (SMTP_PASS === 'your_app_password' ? "[STILL PLACEHOLDER!]" : "[CONFIGURED]") . "\n\n";

if (SMTP_USER === 'your_email@gmail.com' || SMTP_PASS === 'your_app_password') {
    die("ERROR: You have not configured your real Gmail/App Password in backend/db.php yet!\n");
}

$to = SMTP_USER; // Send test to yourself
$subject = "CineSphere SMTP Test";
$message = "<h1>It Works!</h1><p>If you see this, your SMTP settings in CineSphere are correct.</p>";

echo "Attempting to send test email to $to...\n";

$mailer = new SmtpMailer(SMTP_HOST, SMTP_PORT, SMTP_USER, SMTP_PASS);
$result = $mailer->send($to, $subject, $message);

if ($result) {
    echo "SUCCESS: Test email sent! Please check your inbox (and spam folder).\n";
} else {
    echo "FAILURE: The email could not be sent.\n";
    echo "Error Detail: " . ($mailer->error ?: "Unknown error") . "\n\n";
    echo "Possible reasons:\n";
    echo "1. Incorrect Gmail App Password (NOT your regular password).\n";
    echo "2. Port 587 is blocked by your ISP or firewall.\n";
    echo "3. PHP 'fsockopen' is disabled in your php.ini.\n";
    echo "4. Your Gmail account has 2FA disabled (Gmail requires 2FA + App Password now).\n";
}
?>
