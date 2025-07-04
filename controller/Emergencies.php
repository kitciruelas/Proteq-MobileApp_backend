<?php
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
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/Emergencies.php';
require_once __DIR__ . '/../config/session.php';

header('Content-Type: application/json');

$model = new Emergencies($conn);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['is_active'])) {
            $result = $model->getByStatus($_GET['is_active']);
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
        if (isset($input['resolve']) && $input['resolve'] == true) {
            // Resolve emergency
            if ($model->resolve($input['emergency_id'], $input['resolution_reason'], $input['resolved_by'])) {
                echo json_encode(['success' => true, 'message' => 'Emergency resolved.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to resolve.']);
            }
        } else {
            // Create emergency
            if ($model->create($input)) {
                echo json_encode(['success' => true, 'message' => 'Emergency created.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create.']);
            }
        }
        break;
    case 'PUT':
        parse_str(file_get_contents('php://input'), $input);
        if (!isset($input['emergency_id'])) {
            echo json_encode(['success' => false, 'message' => 'emergency_id required.']);
            break;
        }
        $emergency_id = $input['emergency_id'];
        if ($model->update($emergency_id, $input)) {
            echo json_encode(['success' => true, 'message' => 'Emergency updated.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update.']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
} 