<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Register a new user
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string $userType
     * @param string $firstName
     * @param string $lastName
     * @param string $department
     * @param string $college
     * @return array
     */
    public function register($username, $email, $password, $userType = 'STUDENT', $firstName = '', $lastName = '', $department = '', $college = '') {
        try {
            // Check if user already exists
            $checkQuery = "SELECT user_id FROM general_users WHERE email = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => false,
                    'message' => 'User already exists with this email'
                ];
            }
            
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user
            $insertQuery = "INSERT INTO general_users (first_name, last_name, user_type, password, email, department, college, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("sssssss", $firstName, $lastName, $userType, $hashedPassword, $email, $department, $college);
            
            if ($insertStmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'User registered successfully',
                    'user_id' => $this->conn->insert_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Registration failed: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Registration error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Login user
     * @param string $email
     * @param string $password
     * @return array
     */
    public function login($email, $password) {
        try {
            // Find user by email
            $query = "SELECT user_id, first_name, last_name, user_type, email, password, department, college, status FROM general_users WHERE email = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
            $user = $result->fetch_assoc();
            
            // Check if user is active
            if ($user['status'] != 1) {
                return [
                    'success' => false,
                    'message' => 'Account is deactivated. Please contact administrator.'
                ];
            }
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Remove password from response
                unset($user['password']);
                
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Invalid email or password'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get user by ID
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        try {
            $query = "SELECT user_id, first_name, last_name, user_type, email, department, college, created_at, status FROM general_users WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return $result->fetch_assoc();
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update user profile
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data) {
        try {
            $allowedFields = ['first_name', 'last_name', 'email', 'department', 'college'];
            $updates = [];
            $types = '';
            $values = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updates[] = "$field = ?";
                    $types .= 's';
                    $values[] = $value;
                }
            }
            
            if (empty($updates)) {
                return [
                    'success' => false,
                    'message' => 'No valid fields to update'
                ];
            }
            
            $values[] = $userId;
            $types .= 'i';
            
            $query = "UPDATE general_users SET " . implode(', ', $updates) . " WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Update failed: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Update error: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Change password
     * @param int $userId
     * @param string $currentPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Get current password
            $query = "SELECT password FROM general_users WHERE user_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'User not found'
                ];
            }
            
            $user = $result->fetch_assoc();
            
            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                return [
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ];
            }
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password
            $updateQuery = "UPDATE general_users SET password = ? WHERE user_id = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $hashedPassword, $userId);
            
            if ($updateStmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Password changed successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Password change failed: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Password change error: ' . $e->getMessage()
            ];
        }
    }

    public function userExistsByEmail($email) {
        $query = "SELECT user_id FROM general_users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }

    /**
     * Reset password by email (for forgot password flow)
     * @param string $email
     * @param string $newPassword
     * @return array
     */
    public function resetPasswordByEmail($email, $newPassword) {
        try {
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            // Update password
            $updateQuery = "UPDATE general_users SET password = ? WHERE email = ?";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->bind_param("ss", $hashedPassword, $email);
            if ($updateStmt->execute()) {
                if ($updateStmt->affected_rows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Password reset successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'No user found with that email'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Password reset failed: ' . $this->conn->error
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Password reset error: ' . $e->getMessage()
            ];
        }
    }
}
?>
