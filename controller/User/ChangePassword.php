<?php
require_once __DIR__ . '/../../model/User.php';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59602',
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


// Get POST input (JSON)
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_id'], $input['currentPassword'], $input['newPassword'])) {
    echo json_encode([
        'success' => false,
        'message' => 'user_id, currentPassword, and newPassword are required'
    ]);
    exit;
}

$userId = $input['user_id'];
$currentPassword = $input['currentPassword'];
$newPassword = $input['newPassword'];

// Optionally: Validate new password strength (e.g., min 6 chars)
if (strlen($newPassword) < 6) {
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 6 characters long'
    ]);
    exit;
}

$userModel = new User();
$result = $userModel->changePassword($userId, $currentPassword, $newPassword);
echo json_encode($result); 