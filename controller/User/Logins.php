<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221',
    'http://localhost:50033',
    'http://localhost:53364',
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


require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../model/User.php';
require_once __DIR__ . '/../../model/Staff.php';

class LoginController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handle login request
     */
    public function login() {
        // Only allow POST requests
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed. Use POST.'
            ]);
            return;
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        // Validate input
        if (!$input || !isset($input['email']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email and password are required'
            ]);
            return;
        }

        $email = trim($input['email']);
        $password = $input['password'];

        // Basic validation
        if (empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email and password cannot be empty'
            ]);
            return;
        }

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid email format'
            ]);
            return;
        }

        // Attempt login as user
        $result = $this->userModel->login($email, $password);

        file_put_contents(__DIR__ . '/debug_login.txt', print_r($result, true), FILE_APPEND);

        if ($result['success']) {
            // Set user session using SessionManager and get token
            $token = SessionManager::setUserSession($result['user']);
            
            // Login successful as user
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'user' => $result['user'] + ['user_type' => 'user'],
                'token' => $token
            ]);
            return;
        }

        // Attempt login as staff if user login failed
        $staffModel = new Staff();
        $staffResult = $staffModel->login($email, $password);
        file_put_contents(__DIR__ . '/debug_login.txt', print_r($staffResult, true), FILE_APPEND);
        if ($staffResult['success']) {
            // Transform staff data to user format
            $staff = isset($staffResult['staff']) ? $staffResult['staff'] : [];
            $staff['user_type'] = isset($staff['role']) ? $staff['role'] : 'staff';
            $staff['status'] = isset($staff['status']) ? ($staff['status'] == 1 ? 1 : 0) : 0;
            
            // Set staff session using SessionManager and get token
            $token = SessionManager::setStaffSession($staff);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $staffResult['message'],
                'user' => $staff,
                'token' => $token
            ]);
            return;
        }

        // Login failed for both
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => $result['message'] . ' / ' . ($staffResult['message'] ?? 'Staff login failed')
        ]);
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new LoginController();
    $controller->login();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}?>
