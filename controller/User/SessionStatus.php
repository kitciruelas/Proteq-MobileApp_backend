<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:51078');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/session.php';

class SessionStatusController {
    
    /**
     * Get current session status
     */
    public function getStatus() {
        // Only allow GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        // Get token from Authorization header
        $token = SessionManager::getTokenFromHeader();
        
        // Check if user is logged in
        if (empty($token) || !SessionManager::isLoggedIn($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'No active session',
                'logged_in' => false
            ]);
            return;
        }
        
        // Check if session has expired
        if (SessionManager::isSessionExpired($token)) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Session expired',
                'logged_in' => false
            ]);
            return;
        }
        
        // Get current user data
        $userData = SessionManager::getCurrentUserData($token);
        
        // Update last activity
        SessionManager::updateActivity($token);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Session is valid',
            'logged_in' => true,
            'user' => $userData
        ]);
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new SessionStatusController();
    $controller->getStatus();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?> 