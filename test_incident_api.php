<?php
// Test file for Incident Report API
header('Content-Type: application/json');

echo "Testing Incident Report API...\n";

// Test 1: Create an incident report
echo "\n=== Test 1: Creating Incident Report ===\n";

$testData = [
    'incident_type' => 'fire',
    'description' => 'Test fire incident',
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
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

// Test 2: Get all incidents
echo "\n=== Test 2: Getting All Incidents ===\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost/api/controller/IncidentReport.php');
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: " . $httpCode . "\n";
echo "Response: " . $response . "\n";

echo "\n=== Test Complete ===\n";
?> 