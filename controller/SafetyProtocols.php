<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221',
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
require_once __DIR__ . '/../model/SafetyProtocols.php';

header('Content-Type: application/json');

$model = new SafetyProtocols($conn);
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['protocol_id'])) {
            $result = $model->getById($_GET['protocol_id']);
            if ($result) {
                echo json_encode(['success' => true, 'data' => $result]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Protocol not found.']);
            }
        } elseif (isset($_GET['type'])) {
            $result = $model->getByType($_GET['type']);
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $data]);
        } else {
            $result = $model->getAll();
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            echo json_encode(['success' => true, 'data' => $data]);
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Handle file upload if present
        if (isset($_FILES['file_attachment'])) {
            $upload_dir = '../uploads/safety_protocols/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['file_attachment']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file_attachment']['tmp_name'], $file_path)) {
                $input['file_attachment'] = $file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
                break;
            }
        }
        
        if ($model->create($input)) {
            echo json_encode(['success' => true, 'message' => 'Safety protocol created successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create safety protocol.']);
        }
        break;
    case 'PUT':
        parse_str(file_get_contents('php://input'), $input);
        if (!isset($input['protocol_id'])) {
            echo json_encode(['success' => false, 'message' => 'protocol_id required.']);
            break;
        }
        $protocol_id = $input['protocol_id'];
        
        // Handle file upload if present
        if (isset($_FILES['file_attachment'])) {
            $upload_dir = '../uploads/safety_protocols/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['file_attachment']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid() . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file_attachment']['tmp_name'], $file_path)) {
                $input['file_attachment'] = $file_name;
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload file.']);
                break;
            }
        }
        
        if ($model->update($protocol_id, $input)) {
            echo json_encode(['success' => true, 'message' => 'Safety protocol updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update safety protocol.']);
        }
        break;
    case 'DELETE':
        parse_str(file_get_contents('php://input'), $input);
        if (!isset($input['protocol_id'])) {
            echo json_encode(['success' => false, 'message' => 'protocol_id required.']);
            break;
        }
        $protocol_id = $input['protocol_id'];
        
        if ($model->delete($protocol_id)) {
            echo json_encode(['success' => true, 'message' => 'Safety protocol deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete safety protocol.']);
        }
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
        break;
} 