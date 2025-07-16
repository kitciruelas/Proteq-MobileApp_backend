<?php
header('Content-Type: application/json');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221',
    'http://localhost:50033',
    'http://localhost:53364',
    // Add more frontend origins as needed
];
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: PUT, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../model/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use PUT.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['user_id'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'user_id is required.'
    ]);
    exit;
}

$userId = (int)$input['user_id'];
$updateData = $input;
unset($updateData['user_id']); // Remove user_id from update fields

if (empty($updateData)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No fields to update.'
    ]);
    exit;
}

$userModel = new User();
$result = $userModel->updateProfile($userId, $updateData);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
} 