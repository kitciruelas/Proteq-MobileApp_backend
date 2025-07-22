<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/IncidentAssign.php';

class IncidentAssignController {
    private $assignModel;
    public function __construct() {
        $this->assignModel = new IncidentAssign();
    }

    /**
     * Assign an incident to a staff member (admin or dispatcher only)
     */
    public function assignIncidentToStaff() {
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();

        // Check authentication
        if (!$token || !SessionManager::isLoggedIn($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required.'
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

        // Only allow admin or dispatcher to assign
        $currentUserType = SessionManager::getCurrentUserType($token);
        if (!in_array($currentUserType, ['admin', 'dispatcher'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. Only admin or dispatcher can assign incidents.'
            ]);
            return;
        }

        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['incident_id']) || !isset($input['staff_id'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'incident_id and staff_id are required'
            ]);
            return;
        }

        $incidentId = intval($input['incident_id']);
        $staffId = intval($input['staff_id']);

        // Assign incident
        $result = $this->assignModel->assignIncidentToStaff($incidentId, $staffId);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        echo json_encode($result);
    }
}

// Routing logic
$controller = new IncidentAssignController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->assignIncidentToStaff();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
} 