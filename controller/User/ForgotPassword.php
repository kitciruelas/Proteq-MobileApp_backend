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
require_once __DIR__ . '/../../config/session.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['email'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email is required.']);
    exit;
}
$email = trim($input['email']);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
    exit;
}

$userModel = new User();
if (!$userModel->userExistsByEmail($email)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No user found with that email.']);
    exit;
}

// Generate OTP
$otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expires = time() + 600; // 10 minutes from now

try {
    $userData = $userModel->getUserByEmail($email);
    $user_id = $userData['user_id'];
    $otpSet = $userModel->setUserOtp($user_id, $otp, $expires);

    if (!$otpSet) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to store OTP.']);
        exit;
    }

    $otpData = [
        'recipient_name' => $email,
        'otp_code' => $otp,
        'expiration_minutes' => 10
    ];
    $sent = sendOtpEmail($email, $otpData);

    if ($sent) {
        echo json_encode([
            'success' => true,
            'message' => 'OTP sent to your email.',
            'otp' => $otp
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send OTP email.'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}