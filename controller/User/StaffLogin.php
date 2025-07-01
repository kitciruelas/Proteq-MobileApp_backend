<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once __DIR__ . '/../../model/Staff.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['email'], $input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'email and password are required']);
    exit();
}

$staffModel = new Staff();
$result = $staffModel->login($input['email'], $input['password']);

if ($result['success']) {
    // Transform staff data to user format
    if (isset($result['staff'])) {
        $result['user'] = $result['staff'];
        $result['user']['user_type'] = isset($result['staff']['role']) ? $result['staff']['role'] : null;
        unset($result['staff']); // Remove the old key
    } else {
        $result['user'] = ['user_type' => null];
    }
    echo json_encode($result);
} else {
    http_response_code(401);
    $result['user'] = ['user_type' => null];
    echo json_encode($result);
}