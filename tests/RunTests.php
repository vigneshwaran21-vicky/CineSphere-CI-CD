<?php

$passed = 0;
$failed = 0;

function assert_test($condition, $test_name) {
    global $passed, $failed;
    if ($condition) {
        echo "[PASS] $test_name\n";
        $passed++;
    } else {
        echo "[FAIL] $test_name\n";
        $failed++;
    }
}

// Mock test scenarios based on backend logic
echo "Running Unit Tests...\n\n";

// Test 1: Password hashing correctness
$password = "Secret#123";
$hashed = password_hash($password, PASSWORD_DEFAULT);
assert_test(password_verify($password, $hashed), "Test 1: Password Verification Test");

// Test 2: Incorrect Password Rejection
assert_test(!password_verify("wrongpassword", $hashed), "Test 2: Incorrect Password Rejection");

// Define our Regular Expressions
$email_regex = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/';
$password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/';
$username_regex = '/^[a-zA-Z0-9_]{3,15}$/';

// Test 3: Strong Password Regex Validation
// Check if it has 1 uppercase, 1 lowercase, 1 number, 1 special char, and is 8+ chars.
$strong_pass = "Secret#123";
assert_test(preg_match($password_regex, $strong_pass), "Test 3: Strong Password Regex Validation Passed");

// Test 4: Weak Password Regex Rejection
$weak_pass = "password"; // Missing uppercase, number, special char
assert_test(!preg_match($password_regex, $weak_pass), "Test 4: Weak Password Regex Rejection Passed");

// Test 5: Email Regex Validation
$email_valid = "user@gmail.com";
assert_test(preg_match($email_regex, $email_valid), "Test 5: Valid Email Regex Validation Passed");

// Test 6: Invalid Email Regex Rejection
$email_invalid = "userexample.com";
assert_test(!preg_match($email_regex, $email_invalid), "Test 6: Invalid Email Regex Rejection (No @)");

// Test 7: Invalid Email Regex Rejection
$email_no_domain = "user@gmail";
assert_test(!preg_match($email_regex, $email_no_domain), "Test 7: Invalid Email Regex Rejection (No Domain)");

// Test 8: Username Regex Validation
$valid_username = "JohnDoe_99";
$invalid_username = "J*hn Doe!"; // Contains space and special chars
assert_test(preg_match($username_regex, $valid_username) && !preg_match($username_regex, $invalid_username), "Test 8: Strict Username Format Regex Tested");

// Test 9: XSS String Sanitization
$malicious_input = "<script>alert('xss')</script>";
$sanitized = htmlspecialchars($malicious_input, ENT_QUOTES, 'UTF-8');
assert_test(strpos($sanitized, '<script>') === false, "Test 9: XSS Sanitization Successful");

// Test 10: JSON Response Formatting
$response = json_encode(["status" => "success", "message" => "Account created"]);
assert_test(strpos($response, '"status":"success"') !== false, "Test 10: JSON Response Encoding Test");

echo "\nTotal Tests: " . ($passed + $failed) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed > 0) {
    exit(1); 
} else {
    exit(0);
}
?>
