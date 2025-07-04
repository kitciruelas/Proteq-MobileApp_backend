<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

class IncidentReport {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    /**
     * Create a new incident report
     * @param array $data
     * @return array
     */
    public function createIncident($data, $reportedBy = null) {
        try {
            // Use provided reported_by or set to null for anonymous reports
            $reported_by = $reportedBy;
            
            // If no reported_by provided, set to null (anonymous report)
            if ($reported_by === null) {
                $reported_by = null;
            }
            
            $query = "INSERT INTO incident_reports (
                incident_type, description, longitude, latitude, 
                reported_by, priority_level, reporter_safe_status, 
                created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
            
            $stmt = $this->conn->prepare($query);
            
            // Handle null reported_by properly by using a variable
            $reported_by_value = $reported_by;
            $priority_level = $data['priority_level'] ?? 'moderate';
            $reporter_safe_status = $data['reporter_safe_status'] ?? 'unknown';
            
            $stmt->bind_param(
                "ssddsss", 
                $data['incident_type'],
                $data['description'],
                $data['longitude'],
                $data['latitude'],
                $reported_by_value,
                $priority_level,
                $reporter_safe_status
            );
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Incident report created successfully',
                    'incident_id' => $this->conn->insert_id
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to create incident report: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating incident report: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get all incident reports with optional filters
     * @param array $filters
     * @return array
     */
    public function getAllIncidents($filters = []) {
        try {
            $query = "SELECT 
                ir.*,
                gu.first_name as reporter_first_name,
                gu.last_name as reporter_last_name,
                gu.email as reporter_email,
                gu.user_id as reporter_user_id,
                gu.user_type as reporter_user_type,
                CASE 
                    WHEN ir.reported_by IS NULL THEN 'Anonymous'
                    WHEN gu.first_name IS NOT NULL THEN CONCAT(gu.first_name, ' ', gu.last_name)
                    ELSE CONCAT('User ID: ', ir.reported_by)
                END as reporter_name,
                s.name as assigned_staff_name,
                s.role as assigned_staff_role
                FROM incident_reports ir
                LEFT JOIN general_users gu ON ir.reported_by = gu.user_id
                LEFT JOIN staff s ON ir.assigned_to = s.staff_id
                WHERE 1=1";
            
            $params = [];
            $types = '';
            
            // Apply filters
            if (!empty($filters['status'])) {
                $query .= " AND ir.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }
            
            if (!empty($filters['incident_type'])) {
                $query .= " AND ir.incident_type = ?";
                $params[] = $filters['incident_type'];
                $types .= 's';
            }
            
            if (!empty($filters['validation_status'])) {
                $query .= " AND ir.validation_status = ?";
                $params[] = $filters['validation_status'];
                $types .= 's';
            }
            
            if (!empty($filters['priority_level'])) {
                $query .= " AND ir.priority_level = ?";
                $params[] = $filters['priority_level'];
                $types .= 's';
            }
            
            $query .= " ORDER BY ir.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $incidents = [];
            while ($row = $result->fetch_assoc()) {
                $incidents[] = $row;
            }
            
            return [
                'success' => true,
                'message' => 'Incidents retrieved successfully',
                'data' => $incidents,
                'count' => count($incidents)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving incidents: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get incident report by ID
     * @param int $incidentId
     * @return array
     */
    public function getIncidentById($incidentId) {
        try {
            $query = "SELECT 
                ir.*,
                gu.first_name as reporter_first_name,
                gu.last_name as reporter_last_name,
                gu.email as reporter_email,
                gu.user_id as reporter_user_id,
                gu.user_type as reporter_user_type,
                CASE 
                    WHEN ir.reported_by IS NULL THEN 'Anonymous'
                    WHEN gu.first_name IS NOT NULL THEN CONCAT(gu.first_name, ' ', gu.last_name)
                    ELSE CONCAT('User ID: ', ir.reported_by)
                END as reporter_name,
                s.name as assigned_staff_name,
                s.role as assigned_staff_role
                FROM incident_reports ir
                LEFT JOIN general_users gu ON ir.reported_by = gu.user_id
                LEFT JOIN staff s ON ir.assigned_to = s.staff_id
                WHERE ir.incident_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $incidentId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return [
                    'success' => true,
                    'message' => 'Incident retrieved successfully',
                    'data' => $result->fetch_assoc()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Incident not found'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving incident: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update incident report
     * @param int $incidentId
     * @param array $data
     * @return array
     */
    public function updateIncident($incidentId, $data) {
        try {
            $allowedFields = [
                'incident_type', 'description', 'longitude', 'latitude',
                'priority_level', 'reporter_safe_status'
            ];
            
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
            
            $updates[] = "updated_at = NOW()";
            $values[] = $incidentId;
            $types .= 'i';
            
            $query = "UPDATE incident_reports SET " . implode(', ', $updates) . " WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$values);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => 'Incident updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update incident: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating incident: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete incident report
     * @param int $incidentId
     * @return array
     */
    public function deleteIncident($incidentId) {
        try {
            $query = "DELETE FROM incident_reports WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $incidentId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Incident deleted successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Incident not found'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to delete incident: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting incident: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update incident status
     * @param int $incidentId
     * @param string $status
     * @return array
     */
    public function updateStatus($incidentId, $status) {
        try {
            $query = "UPDATE incident_reports SET status = ?, updated_at = NOW() WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $status, $incidentId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Incident status updated successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Incident not found'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update status: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating status: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Validate incident report
     * @param int $incidentId
     * @param string $validationStatus
     * @param string|null $validationNotes
     * @return array
     */
    public function validateIncident($incidentId, $validationStatus, $validationNotes = null) {
        try {
            $query = "UPDATE incident_reports SET 
                validation_status = ?, 
                validation_notes = ?, 
                updated_at = NOW() 
                WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssi", $validationStatus, $validationNotes, $incidentId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Incident validation updated successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Incident not found'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to update validation: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating validation: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Assign incident to staff
     * @param int $incidentId
     * @param int $staffId
     * @return array
     */
    public function assignIncident($incidentId, $staffId) {
        try {
            // First check if staff exists
            $staffQuery = "SELECT staff_id FROM staff WHERE staff_id = ? AND status = 'active'";
            $staffStmt = $this->conn->prepare($staffQuery);
            $staffStmt->bind_param("i", $staffId);
            $staffStmt->execute();
            $staffResult = $staffStmt->get_result();
            
            if ($staffResult->num_rows === 0) {
                return [
                    'success' => false,
                    'message' => 'Staff member not found or inactive'
                ];
            }
            
            $query = "UPDATE incident_reports SET assigned_to = ?, updated_at = NOW() WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $staffId, $incidentId);
            
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    return [
                        'success' => true,
                        'message' => 'Incident assigned successfully'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Incident not found'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to assign incident: ' . $this->conn->error
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error assigning incident: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get incidents by user
     * @param int $userId
     * @return array
     */
    public function getIncidentsByUser($userId) {
        try {
            $query = "SELECT 
                ir.*,
                s.name as assigned_staff_name,
                s.role as assigned_staff_role
                FROM incident_reports ir
                LEFT JOIN staff s ON ir.assigned_to = s.staff_id
                WHERE ir.reported_by = ?
                ORDER BY ir.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $incidents = [];
            while ($row = $result->fetch_assoc()) {
                $incidents[] = $row;
            }
            
            return [
                'success' => true,
                'message' => 'User incidents retrieved successfully',
                'data' => $incidents,
                'count' => count($incidents)
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving user incidents: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get incident statistics
     * @return array
     */
    public function getIncidentStats() {
        try {
            $stats = [];
            
            // Total incidents
            $totalQuery = "SELECT COUNT(*) as total FROM incident_reports";
            $totalResult = $this->conn->query($totalQuery);
            $stats['total'] = $totalResult->fetch_assoc()['total'];
            
            // Incidents by status
            $statusQuery = "SELECT status, COUNT(*) as count FROM incident_reports GROUP BY status";
            $statusResult = $this->conn->query($statusQuery);
            $stats['by_status'] = [];
            while ($row = $statusResult->fetch_assoc()) {
                $stats['by_status'][$row['status']] = $row['count'];
            }
            
            // Incidents by type
            $typeQuery = "SELECT incident_type, COUNT(*) as count FROM incident_reports GROUP BY incident_type";
            $typeResult = $this->conn->query($typeQuery);
            $stats['by_type'] = [];
            while ($row = $typeResult->fetch_assoc()) {
                $stats['by_type'][$row['incident_type']] = $row['count'];
            }
            
            // Incidents by priority
            $priorityQuery = "SELECT priority_level, COUNT(*) as count FROM incident_reports GROUP BY priority_level";
            $priorityResult = $this->conn->query($priorityQuery);
            $stats['by_priority'] = [];
            while ($row = $priorityResult->fetch_assoc()) {
                $stats['by_priority'][$row['priority_level']] = $row['count'];
            }
            
            // Recent incidents (last 7 days)
            $recentQuery = "SELECT COUNT(*) as count FROM incident_reports WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            $recentResult = $this->conn->query($recentQuery);
            $stats['recent_7_days'] = $recentResult->fetch_assoc()['count'];
            
            return [
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => $stats
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving statistics: ' . $e->getMessage()
            ];
        }
    }
}
?> 