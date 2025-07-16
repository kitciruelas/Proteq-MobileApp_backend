<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origins = [
    'http://localhost:59221',
    'http://localhost:50033',
    'http://127.0.0.1:50033',
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

header('Content-Type: application/json');

require_once __DIR__ . '/../../model/User.php';

class SignupController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Handle registration request
     */
    public function signup() {
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
        if (!$input || !isset($input['email']) || !isset($input['password']) || !isset($input['first_name']) || !isset($input['last_name'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email, password, first_name, and last_name are required'
            ]);
            return;
        }
        
        $email = trim($input['email']);
        $password = $input['password'];
        $firstName = trim($input['first_name']);
        $lastName = trim($input['last_name']);
        $userType = isset($input['user_type']) ? strtoupper($input['user_type']) : 'STUDENT';
        $department = isset($input['department']) ? trim($input['department']) : '';
        $college = isset($input['college']) ? trim($input['college']) : '';
        
        // Basic validation
        if (empty($email) || empty($password) || empty($firstName) || empty($lastName)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email, password, first_name, and last_name cannot be empty'
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
        
        // Validate user type
        $allowedUserTypes = ['STUDENT', 'FACULTY', 'UNIVERSITY_EMPLOYEE'];
        if (!in_array($userType, $allowedUserTypes)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid user type. Must be STUDENT, FACULTY, or UNIVERSITY_EMPLOYEE'
            ]);
            return;
        }
        
        // Validate password strength (minimum 6 characters)
        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Password must be at least 6 characters long'
            ]);
            return;
        }
        
        // Validate name length
        if (strlen($firstName) < 2 || strlen($lastName) < 2) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'First name and last name must be at least 2 characters long'
            ]);
            return;
        }
        
        // Set default values for department and college based on user type
        if ($userType === 'UNIVERSITY_EMPLOYEE' && empty($department)) {
            $department = 'N/A';
        }
        if ($userType === 'UNIVERSITY_EMPLOYEE' && empty($college)) {
            $college = 'Not Applicable';
        }
        
        // Attempt registration
        $result = $this->userModel->register(
            $email, // username parameter (not used in new structure)
            $email,
            $password,
            $userType,
            $firstName,
            $lastName,
            $department,
            $college
        );
        
        if ($result['success']) {
            // Registration successful
            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => $result['message'],
                'user_id' => $result['user_id']
            ]);
        } else {
            // Registration failed
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $result['message']
            ]);
        }
    }
}

// Handle the request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new SignupController();
    $controller->signup();
} else {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
