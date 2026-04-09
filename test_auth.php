<?php
// Test script for auth.php API
$data = json_encode([
    "name" => "Test User",
    "email" => "test@example.com",
    "password" => "password123"
]);

$ch = curl_init('http://localhost/CineSphere-Agile/backend/auth.php?action=register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpcode . "\n";
echo "Response: " . $response . "\n";
?>
