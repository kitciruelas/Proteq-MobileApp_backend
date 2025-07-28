<?php
require_once __DIR__ . '/../config/db.php';

class StaffLocation {
    private $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Update staff location
     * @param int $staffId
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public function updateLocation($staffId, $latitude, $longitude) {
        try {
            // Validate coordinates
            if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                return ['success' => false, 'message' => 'Coordinates out of valid range'];
            }

            // Check if a record exists for this staff
            $checkQuery = "SELECT id FROM staff_locations WHERE staff_id = ?";
            $checkStmt = $this->conn->prepare($checkQuery);
            
            if (!$checkStmt) {
                return ['success' => false, 'message' => 'Database prepare error: ' . $this->conn->error];
            }
            
            $checkStmt->bind_param("i", $staffId);
            $checkStmt->execute();
            $checkStmt->store_result();
            
            $recordExists = $checkStmt->num_rows > 0;
            $checkStmt->close();
            
            if ($recordExists) {
                // UPDATE: If record exists, update it
                $query = "UPDATE staff_locations SET latitude = ?, longitude = ?, last_updated = CURRENT_TIMESTAMP WHERE staff_id = ?";
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    return ['success' => false, 'message' => 'Database prepare error: ' . $this->conn->error];
                }
                
                $stmt->bind_param("ddi", $latitude, $longitude, $staffId);
            } else {
                // INSERT: If record doesn't exist, insert new one
                $query = "INSERT INTO staff_locations (staff_id, latitude, longitude, last_updated) VALUES (?, ?, ?, CURRENT_TIMESTAMP)";
                $stmt = $this->conn->prepare($query);
                
                if (!$stmt) {
                    return ['success' => false, 'message' => 'Database prepare error: ' . $this->conn->error];
                }
                
                $stmt->bind_param("idd", $staffId, $latitude, $longitude);
            }
            
            if (!$stmt->execute()) {
                return ['success' => false, 'message' => 'Database execute error: ' . $stmt->error];
            }
            
            $stmt->close();
            
            return [
                'success' => true, 
                'message' => 'Location updated successfully',
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Location update error: ' . $e->getMessage()];
        }
    }

    /**
     * Get staff location
     * @param int $staffId
     * @return array|null
     */
    public function getLocation($staffId) {
        try {
            $query = "SELECT staff_id, latitude, longitude, last_updated FROM staff_locations WHERE staff_id = ?";
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

    /**
     * Get all staff locations
     * @return array
     */
    public function getAllLocations() {
        try {
            $query = "SELECT sl.staff_id, s.name, s.role, sl.latitude, sl.longitude, sl.last_updated 
                      FROM staff_locations sl 
                      JOIN staff s ON sl.staff_id = s.staff_id 
                      ORDER BY sl.last_updated DESC";
            $result = $this->conn->query($query);
            
            $locations = [];
            while ($row = $result->fetch_assoc()) {
                $locations[] = $row;
            }
            
            return ['success' => true, 'data' => $locations];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error fetching locations: ' . $e->getMessage()];
        }
    }

    /**
     * Delete staff location
     * @param int $staffId
     * @return array
     */
    public function deleteLocation($staffId) {
        try {
            $query = "DELETE FROM staff_locations WHERE staff_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $staffId);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Location deleted successfully'];
            } else {
                return ['success' => false, 'message' => 'Delete failed: ' . $this->conn->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Delete error: ' . $e->getMessage()];
        }
    }
} 