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

// Test Case 1: Password hashing correctness
$password = "Secret#123";
$hashed = password_hash($password, PASSWORD_DEFAULT);
assert_test(password_verify($password, $hashed), "Password Verification Test");

// Test Case 2: Email format validation
$email_valid = "user@example.com";
$email_invalid = "userexample.com";
assert_test(filter_var($email_valid, FILTER_VALIDATE_EMAIL), "Valid Email Format Test");
assert_test(!filter_var($email_invalid, FILTER_VALIDATE_EMAIL), "Invalid Email Rejection Test");

// Test Case 3: JSON Response Formatting
$response = json_encode(["status" => "success", "message" => "Test"]);
assert_test(strpos($response, 'success') !== false, "JSON Response Encoding Test");

echo "\nTotal Tests: " . ($passed + $failed) . "\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

if ($failed > 0) {
    exit(1); // Fail build if any test fails
} else {
    exit(0);
}
?>
