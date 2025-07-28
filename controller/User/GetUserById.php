<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../model/User.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed. Use GET.']);
    exit;
}

if (!isset($_GET['user_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'user_id is required.']);
    exit;
}

$userId = (int)$_GET['user_id'];
$userModel = new User();
$user = $userModel->getUserById($userId);

if ($user) {
    http_response_code(200);
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'User not found.']);
} 