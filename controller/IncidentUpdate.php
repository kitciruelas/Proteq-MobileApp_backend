<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../model/IncidentUpdate.php';

class IncidentUpdateController {
    private $updateModel;
    public function __construct() {
        $this->updateModel = new IncidentUpdate();
    }

    /**
     * Update an incident report
     */
    public function updateIncident() {
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();

        // Check authentication
        if (!$token || !SessionManager::isLoggedIn($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Authentication required. Please login to update incidents.'
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

        // Only allow staff or admin to update
        $currentUserType = SessionManager::getCurrentUserType($token);
        if (!in_array($currentUserType, ['admin', 'dispatcher', 'nurse', 'paramedic', 'security', 'firefighter', 'others'])) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Access denied. Only authorized users can update incidents.'
            ]);
            return;
        }

        // Get input data
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input || !isset($input['incident_id']) || !isset($input['update_fields']) || !is_array($input['update_fields'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'incident_id and update_fields (as array) are required.'
            ]);
            return;
        }

        $incidentId = intval($input['incident_id']);
        $data = $input['update_fields'];

        // Update incident
        $result = $this->updateModel->updateIncident($incidentId, $data);

        if ($result['success']) {
            http_response_code(200);
        } else {
            http_response_code(400);
        }
        echo json_encode($result);
    }
}

// Routing logic
$controller = new IncidentUpdateController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->updateIncident();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed. Use POST.'
    ]);
} 