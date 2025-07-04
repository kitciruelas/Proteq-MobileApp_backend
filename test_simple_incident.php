<?php
/**
 * Simple test for incident creation without authentication
 */

// Simulate a POST request with JSON data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Test data
$testData = [
    'incident_type' => 'medical',
    'description' => 'Test incident from Flutter app',
    'longitude' => 120.9842,
    'latitude' => 14.5995,
    'priority_level' => 'moderate',
    'reporter_safe_status' => 'safe'
];

// Simulate JSON input
$input = json_encode($testData);
file_put_contents('php://input', $input);

// Include the controller
ob_start();
include __DIR__ . '/controller/IncidentReport.php';
$output = ob_get_clean();

echo "Test Result:\n";
echo $output . "\n";
?> 