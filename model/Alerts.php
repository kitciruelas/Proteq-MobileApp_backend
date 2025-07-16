<?php
require_once __DIR__ . '/../config/db.php';

class Alerts {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Get the most recent active alert
     * @return array
     */
    public function getLatestActiveAlert() {
        try {
            $query = "SELECT * FROM alerts 
                     WHERE status = 'active' 
                     ORDER BY updated_at DESC, created_at DESC 
                     LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Latest active alert retrieved successfully',
                    'data' => $result->fetch_assoc()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No active alerts found',
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving latest active alert: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get all active alerts
     * @return array
     */
    public function getAllActiveAlerts() {
        try {
            $query = "SELECT * FROM alerts 
                     WHERE status = 'active' 
                     ORDER BY updated_at DESC, created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $alerts = [];
            while ($row = $result->fetch_assoc()) {
                $alerts[] = $row;
            }
            
            return [
                'success' => true,
                'message' => 'Active alerts retrieved successfully',
                'data' => $alerts,
                'count' => count($alerts)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving active alerts: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
    
    /**
     * Get alert by ID
     * @param int $alertId
     * @return array
     */
    public function getAlertById($alertId) {
        try {
            $query = "SELECT * FROM alerts WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $alertId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Alert retrieved successfully',
                    'data' => $result->fetch_assoc()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Alert not found',
                    'data' => null
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving alert: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    
    /**
     * Get alerts by type (active only)
     * @param string $alertType
     * @return array
     */
    public function getActiveAlertsByType($alertType) {
        try {
            $query = "SELECT * FROM alerts 
                     WHERE status = 'active' AND alert_type = ? 
                     ORDER BY updated_at DESC, created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $alertType);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $alerts = [];
            while ($row = $result->fetch_assoc()) {
                $alerts[] = $row;
            }
            
            return [
                'success' => true,
                'message' => 'Active alerts by type retrieved successfully',
                'data' => $alerts,
                'count' => count($alerts)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving alerts by type: ' . $e->getMessage(),
                'data' => []
            ];
        }
    }
}
?> 