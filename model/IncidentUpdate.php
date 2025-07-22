<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

class IncidentUpdate {
    private $conn;
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }

    /**
     * Update incident report
     * @param int $incidentId
     * @param array $data
     * @return array
     */
    public function updateIncident($incidentId, $data) {
        // Check for valid incidentId
        if (empty($incidentId) || !is_numeric($incidentId)) {
            return [
                'success' => false,
                'message' => 'Incident ID is missing. Cannot update incident.'
            ];
        }
        $allowedFields = [
            'incident_type', 'description', 'longitude', 'latitude',
            'priority_level', 'reporter_safe_status', 'status', 'validation_status', 'validation_notes'
        ];
        $updates = [];
        $types = '';
        $values = [];

        // Fetch current validation_status from DB
        $currentStatus = null;
        $statusQuery = "SELECT validation_status FROM incident_reports WHERE incident_id = ?";
        $statusStmt = $this->conn->prepare($statusQuery);
        $statusStmt->bind_param('i', $incidentId);
        $statusStmt->execute();
        $statusResult = $statusStmt->get_result();
        if ($row = $statusResult->fetch_assoc()) {
            $currentStatus = strtolower($row['validation_status']);
        }

        $isValidationUpdate = isset($data['validation_status']);
        $isRejected = $isValidationUpdate && strtolower($data['validation_status']) === 'rejected';
        $isValidated = $isValidationUpdate && strtolower($data['validation_status']) === 'validated';

        // If not a validation update, only allow edit if already validated
        if (!$isValidationUpdate) {
            if ($currentStatus !== 'validated') {
                return [
                    'success' => false,
                    'message' => 'Incident must be validated before editing details.'
                ];
            }
        }

        // If validation_status is being set to rejected, only allow validation_status and status
        if ($isRejected) {
            // Allow validation_status, status, and validation_notes
            $filteredData = [];
            foreach ($data as $field => $value) {
                if (in_array($field, ['validation_status', 'status', 'validation_notes'])) {
                    $filteredData[$field] = $value;
                }
            }
            $data = $filteredData;
        }

        foreach ($data as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $updates[] = "$field = ?";
                $types .= 's';
                $values[] = $value;
            }
        }
        // If validation_status is set to 'rejected', also set status to 'closed'
        if ($isRejected) {
            if (!isset($data['status'])) {
                $updates[] = "status = ?";
                $types .= 's';
                $values[] = 'closed';
            }
        }
        // If validation_status is set to 'validated', allow editing priority_level and reporter_safe_status
        // (No extra logic needed, as these fields are already allowed by default. Just document this.)
        // If status is set to 'resolved', allow as normal (already handled by allowedFields)
        // Note: If validation_status is 'validated' but priority_level or reporter_safe_status are not provided,
        // we do not require them, just allow them if present.
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
    }
} 