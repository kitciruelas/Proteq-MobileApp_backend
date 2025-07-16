<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221',
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

header('Content-Type: application/json');

require_once __DIR__ . '/../model/Alerts.php';

class AlertsController {
    private $alertsModel;
    
    public function __construct() {
        $this->alertsModel = new Alerts();
    }
    
    /**
     * Get the latest active alert (most recently updated/created)
     */
    public function getLatestActiveAlert() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        $result = $this->alertsModel->getLatestActiveAlert();
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode($result);
        }
    }
    
    /**
     * Get all active alerts
     */
    public function getAllActiveAlerts() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        $result = $this->alertsModel->getAllActiveAlerts();
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }
    
    /**
     * Get alert by ID
     */
    public function getAlertById() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        // Get alert ID from URL parameter
        $alertId = isset($_GET['id']) ? (int)$_GET['id'] : null;
        
        if (!$alertId || $alertId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid alert ID is required'
            ]);
            return;
        }
        
        $result = $this->alertsModel->getAlertById($alertId);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode($result);
        }
    }
    
    /**
     * Get active alerts by type
     */
    public function getActiveAlertsByType() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        // Get alert type from URL parameter
        $alertType = isset($_GET['type']) ? trim($_GET['type']) : null;
        
        if (!$alertType || empty($alertType)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Alert type is required'
            ]);
            return;
        }
        
        $result = $this->alertsModel->getActiveAlertsByType($alertType);
        
        if ($result['success']) {
            http_response_code(200);
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }
}

// Handle the request based on the endpoint
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);
$pathSegments = explode('/', trim($path, '/'));

// Get the last segment as the endpoint
$endpoint = end($pathSegments);

$controller = new AlertsController();

switch ($endpoint) {
    case 'latest':
        $controller->getLatestActiveAlert();
        break;
    case 'all':
        $controller->getAllActiveAlerts();
        break;
    case 'by-id':
        $controller->getAlertById();
        break;
    case 'by-type':
        $controller->getActiveAlertsByType();
        break;
    default:
        // Default to latest active alert
        $controller->getLatestActiveAlert();
        break;
}
?> 