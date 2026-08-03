<?php

use PHPUnit\Framework\TestCase;

class CineSphereTest extends TestCase {
    
    public function testPasswordHashing() {
        $password = "Secret#123";
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $this->assertTrue(password_verify($password, $hashed), "Password should be verified correctly.");
        $this->assertFalse(password_verify("wrongpassword", $hashed), "Incorrect password should be rejected.");
    }

    public function testStrongPasswordRegex() {
        $password_regex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/';
        $this->assertEquals(1, preg_match($password_regex, "Secret#123"), "Strong password should match regex.");
        $this->assertEquals(0, preg_match($password_regex, "password"), "Weak password should fail regex.");
    }

    public function testEmailRegex() {
        $email_regex = '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/';
        $this->assertEquals(1, preg_match($email_regex, "user@gmail.com"), "Valid email should pass.");
        $this->assertEquals(0, preg_match($email_regex, "userexample.com"), "Email missing @ should fail.");
    }

    public function testUsernameRegex() {
        $username_regex = '/^[a-zA-Z0-9_]{3,15}$/';
        $this->assertEquals(1, preg_match($username_regex, "JohnDoe_99"), "Valid username should pass.");
        $this->assertEquals(0, preg_match($username_regex, "J*hn Doe!"), "Username with spaces and special chars should fail.");
    }

    public function testXssSanitization() {
        $malicious_input = "<script>alert('xss')</script>";
        $sanitized = htmlspecialchars($malicious_input, ENT_QUOTES, 'UTF-8');
        $this->assertStringNotContainsString("<script>", $sanitized, "Sanitized string should not contain HTML tags.");
    }
}
