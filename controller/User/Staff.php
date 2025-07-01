<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
// ...rest of your PHP code...
require_once __DIR__ . '/../../model/Staff.php';

class StaffController {
    private $staffModel;
    public function __construct() {
        $this->staffModel = new Staff();
    }

    // Get staff by ID
    public function get($id) {
        $staff = $this->staffModel->getById($id);
        if ($staff) {
            echo json_encode(['success' => true, 'staff' => $staff]);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Staff not found']);
        }
    }

    // Update staff
    public function update($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No data provided']);
            return;
        }
        $result = $this->staffModel->update($id, $input);
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    // Delete staff
    public function delete($id) {
        $result = $this->staffModel->delete($id);
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    }

    // List all staff
    public function list() {
        $staff = $this->staffModel->getAll();
        echo json_encode(['success' => true, 'staff' => $staff]);
    }

    // Staff login
    public function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['email'], $input['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'email and password are required']);
            return;
        }
        $result = $this->staffModel->login($input['email'], $input['password']);
        if ($result['success']) {
            // Add user_type to staff object if present
            if (isset($result['staff']) && isset($result['staff']['role'])) {
                $result['staff']['user_type'] = $result['staff']['role'];
            }
            echo json_encode($result);
        } else {
            http_response_code(401);
            echo json_encode($result);
        }
    }
}

// Routing
$controller = new StaffController();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Default to login if no action is provided
    if (!isset($_GET['action']) || $_GET['action'] === 'login') {
        $controller->login();
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} elseif ($method === 'GET') {
    if (isset($_GET['id'])) {
        $controller->get((int)$_GET['id']);
    } else {
        $controller->list();
    }
} elseif ($method === 'PUT') {
    if (isset($_GET['id'])) {
        $controller->update((int)$_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required for update']);
    }
} elseif ($method === 'DELETE') {
    if (isset($_GET['id'])) {
        $controller->delete((int)$_GET['id']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID required for delete']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 