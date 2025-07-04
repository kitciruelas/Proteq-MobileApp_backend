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
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if database connection is available
try {
    require_once __DIR__ . '/../config/session.php';
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
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();
        
        // Set user info based on authentication status
        if ($token && SessionManager::isLoggedIn($token)) {
            // Check if session has expired
            if (SessionManager::isSessionExpired($token)) {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Session expired. Please login again.'
                ]);
                return;
            }
            
            $currentUserId = SessionManager::getCurrentUserId($token);
            $currentUserType = SessionManager::getCurrentUserType($token);
            $currentUserEmail = SessionManager::getCurrentUserEmail($token);
            $currentUserName = SessionManager::getCurrentUserName($token);
            
            // Update session activity
            SessionManager::updateActivity($token);
            
            // Log the authenticated user info for debugging
            error_log("Incident report by authenticated user: ID=$currentUserId, Type=$currentUserType, Email=$currentUserEmail, Name=$currentUserName");
        } else {
            // Require authentication for incident reports
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required. Please login to submit incident reports.'
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
            return;
        }

        $input = null;
        // Debug: log raw input for troubleshooting
        $rawInput = file_get_contents('php://input');
        file_put_contents(__DIR__ . '/../debug_last_input.txt', $rawInput);
        $input = json_decode($rawInput, true);

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

        // Validate priority_level if provided
        $validPriorityLevels = ['low', 'moderate', 'high', 'critical'];
        if (isset($input['priority_level'])) {
            if (!in_array(strtolower($input['priority_level']), $validPriorityLevels)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid priority level'
                ]);
                return;
            }
            // Normalize value
            $input['priority_level'] = strtolower($input['priority_level']);
        }

        // Validate reporter_safe_status if provided
        $validSafeStatuses = ['safe', 'injured', 'unknown'];
        if (isset($input['reporter_safe_status'])) {
            if (!in_array(strtolower($input['reporter_safe_status']), $validSafeStatuses)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid reporter safe status'
                ]);
                return;
            }
            // Normalize value
            $input['reporter_safe_status'] = strtolower($input['reporter_safe_status']);
        }

        $result = $this->incidentModel->createIncident($input, $currentUserId);

        if ($result['success']) {
            // Add user information to the response
            $result['reported_by'] = [
                'user_id' => $currentUserId,
                'user_type' => $currentUserType,
                'email' => $currentUserEmail,
                'name' => $currentUserName
            ];
            
            http_response_code(201);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }
}

// Route the request to the appropriate method
$controller = new IncidentReportController();

// For POST requests, always route to createIncident
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->createIncident();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
}
?>