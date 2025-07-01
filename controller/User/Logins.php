<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

        if ($result['success']) {
            // Login successful as user
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'user' => $result['user'] + ['user_type' => 'user']
            ]);
            return;
        }

        // Attempt login as staff if user login failed
        $staffModel = new Staff();
        $staffResult = $staffModel->login($email, $password);
        if ($staffResult['success']) {
            // Transform staff data to user format
            $user = isset($staffResult['staff']) ? $staffResult['staff'] : [];
            $user['user_type'] = isset($user['role']) ? $user['role'] : 'staff';
            $user['status'] = isset($user['status']) ? ($user['status'] == 1 ? 1 : 0) : 0;
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => $staffResult['message'],
                'user' => $user
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
