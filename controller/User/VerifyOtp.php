<?php
header('Content-Type: application/json');
error_reporting(0); // Suppress warnings/notices for clean JSON

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:64939',
];

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}



if ($otp === $expected_otp) {
    echo json_encode(['success' => true, 'message' => 'OTP is valid.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
} 