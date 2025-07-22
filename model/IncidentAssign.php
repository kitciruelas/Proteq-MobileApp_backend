<?php
require_once __DIR__ . '/../config/db.php';

class IncidentAssign {
    private $conn;
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Assign an incident to a staff member
     * @param int $incidentId
     * @param int $staffId
     * @return array
     */
    public function assignIncidentToStaff($incidentId, $staffId) {
        try {
            // Check if staff exists and is active
            $staffQuery = "SELECT staff_id, email, name FROM staff WHERE staff_id = ? AND status = 'active'";
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
            $staffRow = $staffResult->fetch_assoc();
            $staffEmail = $staffRow['email'];
            $staffName = $staffRow['name'];

            // Assign the incident
            $query = "UPDATE incident_reports SET assigned_to = ?, status = 'in_progress', updated_at = NOW() WHERE incident_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $staffId, $incidentId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    // Send email notification to staff using StaffNotification class
                    require_once __DIR__ . '/StaffNotification.php';
                    $notifier = new StaffNotification();
                    $notifier->sendIncidentAssignmentEmail($staffEmail, $staffName, $incidentId);
                    return [
                        'success' => true,
                        'message' => 'Incident assigned to staff successfully and notification sent'
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => 'Incident not found or already assigned'
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
} 