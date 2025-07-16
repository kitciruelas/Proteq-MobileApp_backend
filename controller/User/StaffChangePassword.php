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
    header("Access-Control-Allow-Methods: POST, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../model/Staff.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['staff_id'], $input['currentPassword'], $input['newPassword'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'staff_id, currentPassword, and newPassword are required.'
    ]);
    exit;
}

$staffId = (int)$input['staff_id'];
$currentPassword = $input['currentPassword'];
$newPassword = $input['newPassword'];

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'New password must be at least 6 characters long.'
    ]);
    exit;
}

$staffModel = new Staff();
$result = $staffModel->changePassword($staffId, $currentPassword, $newPassword);

if ($result['success']) {
    http_response_code(200);
    echo json_encode($result);
} else {
    http_response_code(400);
    echo json_encode($result);
} 