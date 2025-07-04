<?php
// Test file for Incident Report API with Authentication
header('Content-Type: application/json');

echo "Testing Incident Report API with Authentication...\n";

// Step 1: Login to get authentication token
echo "\n=== Step 1: Login to get token ===\n";

$loginData = [
    'email' => 'test@example.com', // Replace with actual test user email
    'password' => 'password123'     // Replace with actual test user password
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/controller/User/Logins.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Code: " . $httpCode . "\n";
echo "Login Response: " . $response . "\n";

$loginResult = json_decode($response, true);
$token = null;

if ($loginResult && isset($loginResult['success']) && $loginResult['success'] && isset($loginResult['token'])) {
    $token = $loginResult['token'];
    echo "Authentication token received: " . substr($token, 0, 20) . "...\n";
} else {
    echo "Login failed! Cannot proceed with incident report test.\n";
    echo "Please check your test user credentials or create a test user first.\n";
    exit();
}

// Step 2: Create an incident report with authentication
echo "\n=== Step 2: Creating Incident Report with Authentication ===\n";

$testData = [
    'incident_type' => 'fire',
    'description' => 'Test fire incident with authentication',
    'longitude' => 121.1564032,
    'latitude' => 14.0804096,
    'priority_level' => 'moderate',
    'reporter_safe_status' => 'safe'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/controller/IncidentReport.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Incident Report HTTP Code: " . $httpCode . "\n";
echo "Incident Report Response: " . $response . "\n";

// Step 3: Test without authentication (should fail)
echo "\n=== Step 3: Testing without Authentication (should fail) ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/controller/IncidentReport.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
    // No Authorization header
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Unauthorized Request HTTP Code: " . $httpCode . "\n";
echo "Unauthorized Request Response: " . $response . "\n";

// Step 4: Logout
echo "\n=== Step 4: Logout ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/controller/User/Logout.php');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer ' . $token
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Logout HTTP Code: " . $httpCode . "\n";
echo "Logout Response: " . $response . "\n";

echo "\n=== Test Complete ===\n";
echo "Summary:\n";
echo "1. Login successful: " . ($token ? "YES" : "NO") . "\n";
echo "2. Incident report with auth: " . ($httpCode == 201 ? "SUCCESS" : "FAILED") . "\n";
echo "3. Incident report without auth: " . ($httpCode == 401 ? "CORRECTLY BLOCKED" : "INCORRECT") . "\n";
echo "4. Logout: " . ($httpCode == 200 ? "SUCCESS" : "FAILED") . "\n";
?> 