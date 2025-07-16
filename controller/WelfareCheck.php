<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/WelfareCheck.php';
require_once __DIR__ . '/../config/session.php';

// Initialize the model
$model = new WelfareCheck($conn);

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221/',
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
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            $result = $model->getByUser($_GET['user_id']);
        } elseif (isset($_GET['emergency_id'])) {
            $result = $model->getByEmergency($_GET['emergency_id']);
        } else {
            $result = $model->getAll();
        }
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if ($model->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Welfare check created.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create.']);
        }
        break;
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['welfare_id'])) {
            echo json_encode(['success' => false, 'message' => 'welfare_id required.']);
            break;
        }
        $welfare_id = $input['welfare_id'];
        if ($model->update($welfare_id, $input)) {
            echo json_encode(['success' => true, 'message' => 'Welfare check updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update.']);
        }
        break;
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['welfare_id'])) {
            echo json_encode(['success' => false, 'message' => 'welfare_id required.']);
            break;
        }
        if ($model->delete($input['welfare_id'])) {
            echo json_encode(['success' => true, 'message' => 'Welfare check deleted.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete.']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
} 