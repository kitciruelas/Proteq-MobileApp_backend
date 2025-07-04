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

require_once __DIR__ . '/../../config/email_helper.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../config/db.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}
$email = trim($input['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

$userModel = new User();
if (!$userModel->userExistsByEmail($email)) {
    echo json_encode(['success' => false, 'message' => 'No user found with that email.']);
    exit;
}

// Generate OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = date('Y-m-d H:i:s', time() + 600); // 10 minutes from now

// Send OTP email
$alertData = [
    'recipient_name' => $email,
    'alert_severity' => 'info',
    'alert_type' => 'Password Reset',
    'title' => 'Password Reset OTP',
    'description' => "Your OTP for password reset is: $otp\nThis code will expire in 10 minutes.",
];
$sent = sendAlertEmail($email, $alertData);

if ($sent) {
    // For demo/testing, include the OTP in the response
    echo json_encode([
        'success' => true,
        'message' => 'OTP sent to your email.',
        'otp' => $otp
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send OTP email.'
    ]);
}