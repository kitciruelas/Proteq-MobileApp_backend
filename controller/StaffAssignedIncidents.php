<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/StaffAssignedIncidents.php';

/**
 * StaffAssignedIncidents API
 * 
 * Query parameters supported:
 *   - status: string (e.g. 'in_progress', 'resolved', etc.)
 *   - priority_level: string
 *   - incident_type: string
 *   - resolved_today: 1 or true (returns only incidents updated today)
 */
class StaffAssignedIncidentsController {
    private $assignedModel;
    public function __construct() {
        $this->assignedModel = new StaffAssignedIncidents();
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
        // Add support for resolved_today filter (only if value is '1' or 'true')
        if (isset($_GET['resolved_today']) && ($_GET['resolved_today'] === '1' || strtolower($_GET['resolved_today']) === 'true')) {
            $filters['resolved_today'] = 1;
        }
        // Get assigned incidents
        $result = $this->assignedModel->getAssignedIncidents($currentUserId, $filters);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }

        echo json_encode($result);
    }
}

// Routing logic
$controller = new StaffAssignedIncidentsController();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->getAssignedIncidents();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use GET.'
    ]);
} 