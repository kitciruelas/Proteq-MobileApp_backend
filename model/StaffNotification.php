<?php
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';

class StaffNotification {
    /**
     * Send an email notification to staff about a new incident assignment
     * @param string $staffEmail
     * @param string $staffName
     * @param int $incidentId
     * @return bool True if sent, false otherwise
     */
    public function sendIncidentAssignmentEmail($staffEmail, $staffName, $incidentId) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer();
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'fking6915@gmail.com';
            $mail->Password = 'azqa bnkd mbop dxgm';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('fking6915@gmail.com', 'Incident System');
            $mail->addAddress($staffEmail, $staffName);
            $mail->Subject = 'New Incident Assigned';
            $mail->Body = 'Hello ' . $staffName . ",\n\nYou have been assigned to a new incident (ID: $incidentId). Please check your dashboard for details.";
            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log or handle email error
            return false;
        }
    }
} 