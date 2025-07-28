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

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../model/User.php';

// IMPORTANT: You must send either user_id OR email, and otp in the request body.
// Example:
//   { "user_id": 123, "otp": "123456" }
//   OR
//   { "email": "user@example.com", "otp": "123456" }

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Check if we have the required fields
    $user_id = null;
    $email = null;
    $otp = isset($input['otp']) ? trim((string)$input['otp']) : '';
    
    // Try to get user_id or email
    if (isset($input['user_id']) && !empty($input['user_id'])) {
        $user_id = (int)$input['user_id'];
    } elseif (isset($input['email']) && !empty($input['email'])) {
        $email = trim($input['email']);
        // Get user_id from email
        $userModel = new User();
        $userData = $userModel->getUserByEmail($email);
        if ($userData) {
            $user_id = $userData['user_id'];
        }
    }
    
    // Validate required fields
    if (!$user_id) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'User ID or email is required.',
            'debug' => 'Please provide either user_id or email in the request body'
        ]);
        exit();
    }
    
    if (!$otp) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'OTP is required.',
            'debug' => 'Please provide otp in the request body'
        ]);
        exit();
    }

    // Use the model to verify OTP
    $userModel = new User();
    $result = $userModel->verifyOtp($user_id, $otp);
    
    // Set the appropriate HTTP status code
    http_response_code($result['status_code']);
    
    // Return the result (remove status_code from response)
    unset($result['status_code']);
    echo json_encode($result);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
} 