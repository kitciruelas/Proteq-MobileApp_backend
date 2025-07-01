<?php
// Start session to access $_SESSION
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if database connection is available
try {
    require_once __DIR__ . '/../config/db.php';
    require_once __DIR__ . '/../model/IncidentReport.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit();
}

class IncidentReportController {
    private $incidentModel;

    public function __construct() {
        try {
            $this->incidentModel = new IncidentReport();
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to initialize controller: ' . $e->getMessage()
            ]);
            exit();
        }
    }

    /**
     * Create a new incident report
     */
    public function createIncident() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
            return;
        }

        // Validate required fields
        $requiredFields = ['incident_type', 'description', 'longitude', 'latitude'];
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => "Field '$field' is required"
                ]);
                return;
            }
        }
        
        // Validate coordinates are numeric
        if (!is_numeric($input['latitude']) || !is_numeric($input['longitude'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Latitude and longitude must be numeric values'
            ]);
            return;
        }

        // Validate incident type
        $validTypes = ['fire', 'earthquake', 'flood', 'typhoon', 'medical', 'security', 'other'];
        if (!in_array(strtolower($input['incident_type']), $validTypes)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid incident type'
            ]);
            return;
        }

        $result = $this->incidentModel->createIncident($input);

        if ($result['success']) {
            http_response_code(201);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Get all incident reports
     */
    public function getAllIncidents() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }

        $filters = [];
        
        // Get query parameters
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['incident_type'])) {
            $filters['incident_type'] = $_GET['incident_type'];
        }
        if (isset($_GET['validation_status'])) {
            $filters['validation_status'] = $_GET['validation_status'];
        }
        if (isset($_GET['priority_level'])) {
            $filters['priority_level'] = $_GET['priority_level'];
        }

        $result = $this->incidentModel->getAllIncidents($filters);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Get incident report by ID
     */
    public function getIncidentById($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $result = $this->incidentModel->getIncidentById($incidentId);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(404);
        }

        echo json_encode($result);
    }

    /**
     * Update incident report
     */
    public function updateIncident($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use PUT.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
            return;
        }

        $result = $this->incidentModel->updateIncident($incidentId, $input);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Delete incident report
     */
    public function deleteIncident($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use DELETE.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $result = $this->incidentModel->deleteIncident($incidentId);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(404);
        }

        echo json_encode($result);
    }

    /**
     * Update incident status
     */
    public function updateStatus($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use PUT.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['status'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Status field is required'
            ]);
            return;
        }

        $result = $this->incidentModel->updateStatus($incidentId, $input['status']);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Validate incident report
     */
    public function validateIncident($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use PUT.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['validation_status'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Validation status field is required'
            ]);
            return;
        }

        $validationNotes = $input['validation_notes'] ?? null;
        $result = $this->incidentModel->validateIncident($incidentId, $input['validation_status'], $validationNotes);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Assign incident to staff
     */
    public function assignIncident($incidentId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use PUT.'
            ]);
            return;
        }

        if (!$incidentId || !is_numeric($incidentId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid incident ID is required'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || !isset($input['staff_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Staff ID field is required'
            ]);
            return;
        }

        $result = $this->incidentModel->assignIncident($incidentId, $input['staff_id']);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Get incidents by user
     */
    public function getIncidentsByUser($userId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }

        if (!$userId || !is_numeric($userId)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Valid user ID is required'
            ]);
            return;
        }

        $result = $this->incidentModel->getIncidentsByUser($userId);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }

    /**
     * Get incident statistics
     */
    public function getIncidentStats() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }

        $result = $this->incidentModel->getIncidentStats();

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }
}

// Handle the request
try {
    $controller = new IncidentReportController();

    // Get the action from query parameter or try to parse from URL
    $action = $_GET['action'] ?? '';

    // If no action in query params, try to parse from URL path
    if (empty($action)) {
        $requestUri = $_SERVER['REQUEST_URI'];
        $path = parse_url($requestUri, PHP_URL_PATH);
        $pathSegments = explode('/', trim($path, '/'));
        
        // Look for the last segment as the action
        $lastSegment = end($pathSegments);
        if ($lastSegment && $lastSegment !== 'IncidentReport.php') {
            $action = $lastSegment;
        }
    }

    // If still no action, check if it's a direct method call
    if (empty($action)) {
        // Default to get all incidents for GET requests
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->getAllIncidents();
            exit();
        }
        // Default to create for POST requests
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->createIncident();
            exit();
        }
    }

    // Handle specific actions
    switch ($action) {
        case 'create':
            $controller->createIncident();
            break;
            
        case 'get_all':
        case 'list':
        case 'incidents':
            $controller->getAllIncidents();
            break;
            
        case 'get_by_id':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->getIncidentById($incidentId);
            }
            break;
            
        case 'update':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->updateIncident($incidentId);
            }
            break;
            
        case 'delete':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->deleteIncident($incidentId);
            }
            break;
            
        case 'update_status':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->updateStatus($incidentId);
            }
            break;
            
        case 'validate':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->validateIncident($incidentId);
            }
            break;
            
        case 'assign':
            $incidentId = $_GET['id'] ?? null;
            if (!$incidentId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Incident ID is required'
                ]);
            } else {
                $controller->assignIncident($incidentId);
            }
            break;
            
        case 'get_by_user':
            $userId = $_GET['user_id'] ?? null;
            if (!$userId) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID is required'
                ]);
            } else {
                $controller->getIncidentsByUser($userId);
            }
            break;
            
        case 'stats':
        case 'statistics':
            $controller->getIncidentStats();
            break;
            
        default:
            // If no specific action, handle based on HTTP method
            if ($_SERVER['REQUEST_METHOD'] === 'GET') {
                $controller->getAllIncidents();
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $controller->createIncident();
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid action. Available actions: create, get_all, get_by_id, update, delete, update_status, validate, assign, get_by_user, stats'
                ]);
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Internal server error: ' . $e->getMessage()
    ]);
}
?> 