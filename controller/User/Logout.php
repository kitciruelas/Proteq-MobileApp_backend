<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/session.php';

class LogoutController {
    
    /**
     * Handle logout request
     */
    public function logout() {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
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
                'message' => 'No active session to logout'
            ]);
            return;
        }
        
        // Get user info before logout for response
        $userData = SessionManager::getCurrentUserData($token);
        
        // Perform logout
        SessionManager::logout($token);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Logout successful',
            'user' => $userData
        ]);
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LogoutController();
    $controller->logout();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?> 