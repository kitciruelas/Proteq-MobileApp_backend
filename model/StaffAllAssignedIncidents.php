<?php
require_once __DIR__ . '/../config/db.php';

class StaffAllAssignedIncidents {
    private $conn;
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Get all incidents ever assigned to a specific staff member (any status)
     * @param int $staffId
     * @param array $filters
     * @return array
     */
    public function getAllAssignedIncidents($staffId, $filters = []) {
        try {
            // Get staff's latest location for distance calculation
            $locQuery = "SELECT latitude, longitude 
                        FROM staff_locations 
                        WHERE staff_id = ? 
                        ORDER BY last_updated DESC 
                        LIMIT 1";
            $locStmt = $this->conn->prepare($locQuery);
            $locStmt->bind_param("i", $staffId);
            $locStmt->execute();
            $location = $locStmt->get_result()->fetch_assoc();
            
            if (!$location) {
                return [
                    'success' => false,
                    'message' => 'Staff location not found. Please update your location first.'
                ];
            }
            
            // Build the main query with distance calculation
            $query = "SELECT 
                ir.incident_id as incident_id,
                ir.incident_type,
                ir.description,
                ir.longitude,
                ir.latitude,
                ir.date_reported,
                ir.status,
                ir.assigned_to,
                ir.reported_by,
                ir.validation_status,
                ir.validation_notes,
                ir.priority_level,
                ir.reporter_safe_status,
                ir.created_at,
                ir.updated_at,
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
                s.role as assigned_staff_role,
                (
                    6371 * acos(
                        cos(radians(?)) *
                        cos(radians(ir.latitude)) *
                        cos(radians(ir.longitude) - radians(?)) +
                        sin(radians(?)) *
                        sin(radians(ir.latitude))
                    )
                ) AS distance_km
                FROM incident_reports ir
                LEFT JOIN general_users gu ON ir.reported_by = gu.user_id
                LEFT JOIN staff s ON ir.assigned_to = s.staff_id
                WHERE ir.assigned_to = ?";
            
            $params = [
                $location['latitude'],
                $location['longitude'],
                $location['latitude'],
                $staffId
            ];
            $types = "dddi";
            
            // Apply filters (but do not add a default status filter)
            if (!empty($filters['status'])) {
                $query .= " AND ir.status = ?";
                $params[] = $filters['status'];
                $types .= 's';
            }
            if (!empty($filters['priority_level'])) {
                $query .= " AND ir.priority_level = ?";
                $params[] = $filters['priority_level'];
                $types .= 's';
            }
            if (!empty($filters['incident_type'])) {
                $query .= " AND ir.incident_type = ?";
                $params[] = $filters['incident_type'];
                $types .= 's';
            }
            // No default status filter here!
            $query .= " ORDER BY 
                CASE ir.priority_level
                    WHEN 'critical' THEN 1
                    WHEN 'high' THEN 2
                    WHEN 'moderate' THEN 3
                    WHEN 'low' THEN 4
                END,
                ir.date_reported DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
            $incidents = [];
            while ($row = $result->fetch_assoc()) {
                if (!isset($row['incident_id'])) {
                    $row['incident_id'] = null;
                }
                if (isset($row['distance_km'])) {
                    $row['distance_km'] = round($row['distance_km'], 2);
                }
                $incidents[] = $row;
            }
            return [
                'success' => true,
                'message' => 'All assigned incidents retrieved successfully',
                'data' => $incidents,
                'count' => count($incidents),
                'staff_location' => [
                    'latitude' => $location['latitude'],
                    'longitude' => $location['longitude']
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error retrieving assigned incidents: ' . $e->getMessage()
            ];
        }
    }
} 