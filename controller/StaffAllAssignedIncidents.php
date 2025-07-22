<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/StaffAllAssignedIncidents.php';

class StaffAllAssignedIncidentsController {
    private $assignedModel;
    public function __construct() {
        $this->assignedModel = new StaffAllAssignedIncidents();
    }

    /**
     * Get all incidents ever assigned to the current staff member (any status)
     */
    public function getAllAssignedIncidents() {
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

        // Get all assigned incidents
        $result = $this->assignedModel->getAllAssignedIncidents($currentUserId, $filters);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }
}

// Routing logic
$controller = new StaffAllAssignedIncidentsController();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getAllAssignedIncidents();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
} 