<?php
require_once __DIR__ . '/../config/email_helper.php';

class StaffNotification {
    /**
     * Send an email notification to staff about a new incident assignment
     * @param string $staffEmail
     * @param string $staffName
     * @param int $incidentId
     * @return bool True if sent, false otherwise
     */
    public function sendIncidentAssignmentEmail($staffEmail, $staffName, $incidentId) {
        // Prepare alert data for the email
        $alertData = [
            'recipient_name' => $staffName,
            'alert_severity' => 'info',
            'alert_type' => 'Incident Assignment',
            'title' => 'New Incident Assigned',
            'description' => "You have been assigned to a new incident (ID: $incidentId). Please check your dashboard for details.",
            // Dummy/default values for required fields (customize as needed)
            'latitude' => 0,
            'longitude' => 0,
            'radius_km' => 0.1
        ];
        return sendAlertEmail($staffEmail, $alertData);
    }
} 