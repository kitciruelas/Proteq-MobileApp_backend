<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../../config/session.php';

class SessionDebugController {
    
    /**
     * Debug session information
     */
    public function debugSession() {
        // Only allow GET requests
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use GET.'
            ]);
            return;
        }
        
        // Start session to get current state
        SessionManager::startSession();
        
        $debugInfo = [
            'session_status' => session_status(),
            'session_id' => session_id(),
            'session_name' => session_name(),
            'session_save_path' => session_save_path(),
            'session_cookie_params' => session_get_cookie_params(),
            'session_data' => $_SESSION,
            'is_logged_in' => SessionManager::isLoggedIn(),
            'current_user_id' => SessionManager::getCurrentUserId(),
            'current_user_type' => SessionManager::getCurrentUserType(),
            'current_user_email' => SessionManager::getCurrentUserEmail(),
            'current_user_name' => SessionManager::getCurrentUserName(),
            'session_expired' => SessionManager::isSessionExpired(),
            'login_time' => $_SESSION['login_time'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'current_time' => time(),
            'time_since_last_activity' => isset($_SESSION['last_activity']) ? (time() - $_SESSION['last_activity']) : null,
            'cookies' => $_COOKIE,
            'headers' => getallheaders(),
            'request_method' => $_SERVER['REQUEST_METHOD'],
            'request_uri' => $_SERVER['REQUEST_URI'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Session debug information',
            'debug_info' => $debugInfo
        ]);
    }
    
    /**
     * Test session creation
     */
    public function testSession() {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data'
            ]);
            return;
        }
        
        $testType = $input['type'] ?? 'user';
        
        if ($testType === 'user') {
            $testData = [
                'user_id' => 999,
                'user_type' => 'STUDENT',
                'email' => 'test@example.com',
                'first_name' => 'Test',
                'last_name' => 'User',
                'department' => 'Test Department',
                'college' => 'Test College',
                'status' => 1
            ];
            SessionManager::setUserSession($testData);
        } else {
            $testData = [
                'staff_id' => 888,
                'role' => 'SECURITY_OFFICER',
                'email' => 'teststaff@example.com',
                'name' => 'Test Staff',
                'availability' => 'ON_DUTY',
                'status' => 1
            ];
            SessionManager::setStaffSession($testData);
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Test session created',
            'session_data' => SessionManager::getCurrentUserData(),
            'test_type' => $testType
        ]);
    }
}

// Handle the request
$controller = new SessionDebugController();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller->debugSession();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->testSession();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?> 