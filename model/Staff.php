<?php
require_once __DIR__ . '/../config/db.php';

class Staff {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    // Create a new staff member
    public function create($name, $email, $password, $role, $availability = 'available', $status = 'active') {
        try {
            // Check if staff already exists
            $checkQuery = "SELECT staff_id FROM staff WHERE email = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $result = $checkStmt->get_result();
            if ($result->num_rows > 0) {
                return ['success' => false, 'message' => 'Staff already exists with this email'];
            }
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = "INSERT INTO staff (name, email, password, role, availability, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $insertStmt = $this->conn->prepare($insertQuery);
            $insertStmt->bind_param("ssssss", $name, $email, $hashedPassword, $role, $availability, $status);
            if ($insertStmt->execute()) {
                return ['success' => true, 'message' => 'Staff created successfully', 'staff_id' => $this->conn->insert_id];
            } else {
                return ['success' => false, 'message' => 'Creation failed: ' . $this->conn->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Creation error: ' . $e->getMessage()];
        }
    }

    // Read staff by ID
    public function getById($staffId) {
        try {
            $query = "SELECT staff_id, name, email, role, availability, status, created_at, updated_at FROM staff WHERE staff_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $staffId);
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

    // Update staff
    public function update($staffId, $data) {
        try {
            $allowedFields = ['name', 'email', 'role', 'availability', 'status'];
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
                return ['success' => false, 'message' => 'No valid fields to update'];
            }
            $values[] = $staffId;
            $types .= 'i';
            $query = "UPDATE staff SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE staff_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$values);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff updated successfully'];
            } else {
                return ['success' => false, 'message' => 'Update failed: ' . $this->conn->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update error: ' . $e->getMessage()];
        }
    }

    // Delete staff
    public function delete($staffId) {
        try {
            $query = "DELETE FROM staff WHERE staff_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $staffId);
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Staff deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Delete failed: ' . $this->conn->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Delete error: ' . $e->getMessage()];
        }
    }

    // List all staff
    public function getAll() {
        try {
            $query = "SELECT staff_id, name, email, role, availability, status, created_at, updated_at FROM staff";
            $result = $this->conn->query($query);
            $staff = [];
            while ($row = $result->fetch_assoc()) {
                $staff[] = $row;
            }
            return $staff;
        } catch (Exception $e) {
            return [];
        }
    }

    // Staff login
    public function login($email, $password) {
        try {
            $query = "SELECT staff_id, name, email, password, role, availability, status, created_at, updated_at FROM staff WHERE email = ?";
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
            $staff = $result->fetch_assoc();
            if ($staff['status'] != 1) {
                return [
                    'success' => false,
                    'message' => 'Account is deactivated. Please contact administrator.'
                ];
            }
            if (password_verify($password, $staff['password'])) {
                unset($staff['password']);
                return [
                    'success' => true,
                    'message' => 'Login successful',
                    'staff' => $staff
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
} 