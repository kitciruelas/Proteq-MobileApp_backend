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
    require_once __DIR__ . '/../model/Staff.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error: ' . $e->getMessage()
    ]);
    exit();
}

class StaffIncidentsController {
    private $incidentModel;
    private $staffModel;

    public function __construct() {
        try {
            $this->incidentModel = new IncidentReport();
            $this->staffModel = new Staff();
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
     * Get incidents assigned to the current staff member
     */
    public function getAssignedIncidents() {
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();
        
        // Check authentication
        if (!$token || !SessionManager::isLoggedIn($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required. Please login to view assigned incidents.'
            ]);
            return;
        }
        
        // Check if session has expired
        if (SessionManager::isSessionExpired($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
            return;
        }
        
        // Get current user info
        $currentUserId = SessionManager::getCurrentUserId($token);
        $currentUserType = SessionManager::getCurrentUserType($token);
        
        // Check if user is staff
        if (!$currentUserId || !in_array($currentUserType, ['nurse', 'paramedic', 'security', 'firefighter', 'others'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. Only staff members can view assigned incidents.'
            ]);
            return;
        }
        
        // Update session activity
        SessionManager::updateActivity($token);
        
        // Get filters from query parameters
        $filters = [];
        if (isset($_GET['status'])) {
            $filters['status'] = $_GET['status'];
        }
        if (isset($_GET['priority_level'])) {
            $filters['priority_level'] = $_GET['priority_level'];
        }
        if (isset($_GET['incident_type'])) {
            $filters['incident_type'] = $_GET['incident_type'];
        }
        
        // Get assigned incidents
        $result = $this->incidentModel->getAssignedIncidents($currentUserId, $filters);
        
        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        
        echo json_encode($result);
    }

    /**
     * Update staff location for distance calculations
     */
    public function updateLocation() {
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();
        
        // Check authentication
        if (!$token || !SessionManager::isLoggedIn($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required. Please login to update location.'
            ]);
            return;
        }
        
        // Check if session has expired
        if (SessionManager::isSessionExpired($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired. Please login again.'
            ]);
            return;
        }
        
        // Get current user info
        $currentUserId = SessionManager::getCurrentUserId($token);
        $currentUserType = SessionManager::getCurrentUserType($token);
        
        // Check if user is staff
        if (!$currentUserId || !in_array($currentUserType, ['nurse', 'paramedic', 'security', 'firefighter', 'others'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. Only staff members can update location.'
            ]);
            return;
        }
        
        // Update session activity
        SessionManager::updateActivity($token);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['latitude']) || !isset($input['longitude'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Latitude and longitude are required'
            ]);
            return;
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
        
        try {
            // Insert or update staff location
            $query = "INSERT INTO staff_locations (staff_id, latitude, longitude, last_updated) 
                     VALUES (?, ?, ?, NOW()) 
                     ON DUPLICATE KEY UPDATE 
                     latitude = VALUES(latitude), 
                     longitude = VALUES(longitude), 
                     last_updated = NOW()";
            
            global $conn;
            $stmt = $conn->prepare($query);
            $stmt->bind_param("idd", $currentUserId, $input['latitude'], $input['longitude']);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Location updated successfully',
                    'location' => [
                        'latitude' => $input['latitude'],
                        'longitude' => $input['longitude'],
                        'staff_id' => $currentUserId
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to update location: ' . $conn->error
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error updating location: ' . $e->getMessage()
            ]);
        }
    }
}

// Route the request to the appropriate method
$controller = new StaffIncidentsController();

// Route based on HTTP method and action
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getAssignedIncidents();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] === 'location') {
        $controller->updateLocation();
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action. Use ?action=location to update location.'
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET for assigned incidents or POST with ?action=location for updating location.'
    ]);
}
?> 