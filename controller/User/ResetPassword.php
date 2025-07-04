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

require_once __DIR__ . '/../../model/User.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'], $input['new_password'])) {
    echo json_encode(['success' => false, 'message' => 'Email, OTP, expected_otp, and new_password are required.']);
    exit;
}

$email = trim($input['email']);

$new_password = $input['new_password'];

if ($otp === $expected_otp) {
    $userModel = new User();
    $result = $userModel->resetPasswordByEmail($email, $new_password);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid OTP.']);
} 