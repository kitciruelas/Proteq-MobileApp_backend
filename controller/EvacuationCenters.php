<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59602',
    'http://localhost:50033',
    'http://127.0.0.1:50033',
    // Add more frontend origins as needed
];
if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Methods: GET, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../model/EvacuationCenters.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit();
}

class EvacuationCentersController {
    private $model;

    public function __construct($db) {
        $this->model = new EvacuationCenters($db);
    }

    // GET /evacuation_centers
    public function getAll() {
        try {
            $centers = $this->model->getAll();
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'data' => $centers
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch evacuation centers: ' . $e->getMessage()
            ]);
        }
    }

    // GET /evacuation_centers/{id}
    public function getById($center_id) {
        try {
            $center = $this->model->getById($center_id);
            if ($center) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'data' => $center
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Evacuation center not found or not open.'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to fetch evacuation center: ' . $e->getMessage()
            ]);
        }
    }
}

$controller = new EvacuationCentersController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['id'])) {
        $controller->getById($_GET['id']);
    } else {
        $controller->getAll();
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
} 