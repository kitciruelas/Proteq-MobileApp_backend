<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/StaffLocation.php';

/**
 * StaffLocation API
 * 
 * Endpoints:
 *   - POST /update: Update staff location
 *   - GET /get: Get current staff location
 *   - GET /all: Get all staff locations (admin only)
 *   - DELETE /delete: Delete staff location
 */
class StaffLocationController {
    private $locationModel;
    
    public function __construct() {
        $this->locationModel = new StaffLocation();
    }

    /**
     * Update staff location
     */
    public function updateLocation() {
        // Set JSON content type header
        header('Content-Type: application/json');
        
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
        // Log the request for debugging
        error_log("Location update request received: " . json_encode($_POST));
        
        // Get staff_id from session or token
        $staff_id = null;
        
        // Try session-based authentication first
        if (isset($_SESSION['staff_id'])) {
            $staff_id = $_SESSION['staff_id'];
        } else {
            // Try token-based authentication
            $token = SessionManager::getTokenFromHeader();
            if ($token && SessionManager::isLoggedIn($token)) {
                $staff_id = SessionManager::getCurrentUserId($token);
                // Update session activity
                SessionManager::updateActivity($token);
            }
        }
        
        // Check if staff is logged in
        if (!$staff_id) {
            error_log("Location update failed: No staff_id found");
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }

        // Get JSON data from request
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Log the received data
        error_log("Location update data received: " . $json);

        // Validate data
        if (!isset($data['latitude']) || !isset($data['longitude'])) {
            error_log("Location update failed: Missing location data");
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing location data']);
            return;
        }

        // Validate coordinates
        $latitude = filter_var($data['latitude'], FILTER_VALIDATE_FLOAT);
        $longitude = filter_var($data['longitude'], FILTER_VALIDATE_FLOAT);

        if ($latitude === false || $longitude === false) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid coordinates']);
            return;
        }

        error_log("Processing location update for staff_id: " . $staff_id);
        
        // Update location using model
        $result = $this->locationModel->updateLocation($staff_id, $latitude, $longitude);
        
        if ($result['success']) {
            error_log("Location update successful for staff_id: " . $staff_id);
            
            // Get the location name using reverse geocoding (if available)
            $locationName = null;
            if (file_exists(__DIR__ . '/../includes/location_utils.php')) {
                require_once __DIR__ . '/../includes/location_utils.php';
                $locationName = getLocationFromCoordinates($latitude, $longitude);
            }
            
            $response = [
                'success' => true, 
                'message' => 'Location updated successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            if ($locationName) {
                $response['locationName'] = $locationName;
            }
            
            echo json_encode($response);
        } else {
            error_log("Location update failed: " . $result['message']);
            http_response_code(500);
            echo json_encode($result);
        }
    }

    /**
     * Get current staff location
     */
    public function getLocation() {
        header('Content-Type: application/json');
        
        // Get staff_id from session or token
        $staff_id = null;
        
        // Try session-based authentication first
        if (isset($_SESSION['staff_id'])) {
            $staff_id = $_SESSION['staff_id'];
        } else {
            // Try token-based authentication
            $token = SessionManager::getTokenFromHeader();
            if ($token && SessionManager::isLoggedIn($token)) {
                $staff_id = SessionManager::getCurrentUserId($token);
                // Update session activity
                SessionManager::updateActivity($token);
            }
        }
        
        // Check if staff is logged in
        if (!$staff_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        $location = $this->locationModel->getLocation($staff_id);
        
        if ($location) {
            echo json_encode([
                'success' => true,
                'data' => $location
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No location found for this staff member'
            ]);
        }
    }

    /**
     * Get all staff locations (admin only)
     */
    public function getAllLocations() {
        header('Content-Type: application/json');
        
        // Get staff_id and role from session or token
        $staff_id = null;
        $role = null;
        
        // Try session-based authentication first
        if (isset($_SESSION['staff_id'])) {
            $staff_id = $_SESSION['staff_id'];
            $role = $_SESSION['role'] ?? null;
        } else {
            // Try token-based authentication
            $token = SessionManager::getTokenFromHeader();
            if ($token && SessionManager::isLoggedIn($token)) {
                $staff_id = SessionManager::getCurrentUserId($token);
                $role = SessionManager::getCurrentUserType($token);
                // Update session activity
                SessionManager::updateActivity($token);
            }
        }
        
        // Check if staff is logged in and is admin
        if (!$staff_id || $role !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
            return;
        }

        $result = $this->locationModel->getAllLocations();
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }

    /**
     * Delete staff location
     */
    public function deleteLocation() {
        header('Content-Type: application/json');
        
        // Get staff_id from session or token
        $staff_id = null;
        
        // Try session-based authentication first
        if (isset($_SESSION['staff_id'])) {
            $staff_id = $_SESSION['staff_id'];
        } else {
            // Try token-based authentication
            $token = SessionManager::getTokenFromHeader();
            if ($token && SessionManager::isLoggedIn($token)) {
                $staff_id = SessionManager::getCurrentUserId($token);
                // Update session activity
                SessionManager::updateActivity($token);
            }
        }
        
        // Check if staff is logged in
        if (!$staff_id) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        $result = $this->locationModel->deleteLocation($staff_id);
        
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(500);
            echo json_encode($result);
        }
    }
}

// Routing logic
$controller = new StaffLocationController();

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $controller->updateLocation();
        break;
    case 'GET':
        if (isset($_GET['action'])) {
            switch ($_GET['action']) {
                case 'get':
                    $controller->getLocation();
                    break;
                case 'all':
                    $controller->getAllLocations();
                    break;
                default:
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Invalid action']);
                    break;
            }
        } else {
            $controller->getLocation(); // Default to get current location
        }
        break;
    case 'DELETE':
        $controller->deleteLocation();
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        break;
} 