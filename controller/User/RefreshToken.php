<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/session.php';

class RefreshTokenController {
    public function refresh() {
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
        
        // Update last activity
        SessionManager::updateActivity($token);
        
        // Get current user data
        $userData = SessionManager::getCurrentUserData($token);
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Session refreshed',
            'logged_in' => true,
            'user' => $userData
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RefreshTokenController();
    $controller->refresh();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
} 