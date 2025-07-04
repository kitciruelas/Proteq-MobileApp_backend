<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function getLocationFromCoordinates($latitude, $longitude) {
    $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat={$latitude}&lon={$longitude}&zoom=18&addressdetails=1";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Proteq Alert System');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if ($data && isset($data['display_name'])) {
            return $data['display_name'];
        }
    }
    
    // If geocoding fails, return coordinates
    return "Latitude: {$latitude}, Longitude: {$longitude}";
}

function sendAlertEmail($userEmail, $alertData) {
    // If $userEmail is an array, send to multiple recipients
    if (is_array($userEmail)) {
        $results = [];
        foreach ($userEmail as $email) {
            try {
                $results[$email] = sendAlertEmail($email, $alertData);
                // Add a small delay between emails
                usleep(100000); // 100ms delay
            } catch (Exception $e) {
                $results[$email] = false;
                error_log("Failed to send email to " . $email . ": " . $e->getMessage());
            }
        }
        return $results;
    }

    $mail = new PHPMailer(true);
    try {
        // Enable debug output
        $mail->SMTPDebug = 3; // Show all debug output
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'fking6915@gmail.com';
        $mail->Password = 'azqa bnkd mbop dxgm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Additional settings for better reliability
        $mail->SMTPKeepAlive = true;
        $mail->Timeout = 60;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Test SMTP connection before proceeding
        if (!$mail->smtpConnect()) {
            throw new Exception('Failed to connect to SMTP server');
        }

        // Set sender and recipient
        $mail->setFrom('alerts.proteq@gmail.com', 'Proteq Alert System');
        $mail->addAddress($userEmail, $alertData['recipient_name']);
        
        // Set subject line based on severity
        $severity = $alertData['alert_severity'] ?? 'warning';
        $severityEmoji = match($severity) {
            'emergency' => '🚨',
            'warning' => '⚠️',
            'info' => 'ℹ️',
            default => '⚠️'
        };
        
        $mail->Subject = $severityEmoji . " " . strtoupper($severity) . " ALERT: " . strtoupper(htmlspecialchars($alertData['alert_type'])) . " - " . strtoupper(htmlspecialchars($alertData['title']));
        
        // Add reply-to header
        $mail->addReplyTo('alerts.proteq@gmail.com', 'Proteq Alert System');

        // Add additional headers to improve deliverability
        $mail->addCustomHeader('X-Priority', $severity === 'emergency' ? '1' : ($severity === 'warning' ? '2' : '3')); // Priority based on severity
        $mail->addCustomHeader('X-MSMail-Priority', $severity === 'emergency' ? 'High' : ($severity === 'warning' ? 'Normal' : 'Low'));
        $mail->addCustomHeader('Importance', $severity === 'emergency' ? 'High' : ($severity === 'warning' ? 'Normal' : 'Low'));
        $mail->addCustomHeader('X-Mailer', 'Proteq Alert System');
        $mail->addCustomHeader('List-Unsubscribe', '<mailto:alerts.proteq@gmail.com>');
        $mail->addCustomHeader('Precedence', 'bulk');

        // Get location from coordinates
        $location = getLocationFromCoordinates($alertData['latitude'], $alertData['longitude']);
        
        // Format coordinates
        $coordinates = number_format($alertData['latitude'], 6) . ', ' . number_format($alertData['longitude'], 6);
        
        // Create Google Maps link
        $mapsLink = "https://www.google.com/maps?q={$alertData['latitude']},{$alertData['longitude']}";
        
        // Get severity-specific styling
        $severityStyles = match($severity) {
            'emergency' => [
                'header_bg' => '#dc3545',
                'border_color' => '#dc3545',
                'text_color' => '#ffffff',
                'alert_icon' => '🚨',
                'alert_title' => 'EMERGENCY ALERT'
            ],
            'warning' => [
                'header_bg' => '#ffc107',
                'border_color' => '#ffc107',
                'text_color' => '#000000',
                'alert_icon' => '⚠️',
                'alert_title' => 'WARNING ALERT'
            ],
            'info' => [
                'header_bg' => '#17a2b8',
                'border_color' => '#17a2b8',
                'text_color' => '#ffffff',
                'alert_icon' => 'ℹ️',
                'alert_title' => 'INFORMATION ALERT'
            ],
            default => [
                'header_bg' => '#ffc107',
                'border_color' => '#ffc107',
                'text_color' => '#000000',
                'alert_icon' => '⚠️',
                'alert_title' => 'ALERT'
            ]
        };
        
        // Build HTML body with severity-specific styling
        $htmlBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;'>
                <div style='background-color: {$severityStyles['header_bg']}; padding: 20px; border-radius: 5px; margin-bottom: 20px;'>
                    <h2 style='color: {$severityStyles['text_color']}; margin: 0; font-size: 24px;'>{$severityStyles['alert_icon']} {$severityStyles['alert_title']}</h2>
                    <p style='color: {$severityStyles['text_color']}; margin: 10px 0 0 0; font-size: 16px;'>This is an automated alert from the university.</p>
                </div>
                
                <div style='background-color: #fff; padding: 20px; border-radius: 5px; border: 1px solid #e0e0e0;'>
                    <h3 style='color: #333; margin-top: 0; font-size: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>Alert Details</h3>
                    <ul style='list-style: none; padding: 0; margin: 0;'>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Type:</strong> <span style='color: {$severityStyles['header_bg']}; font-weight: bold;'>" . htmlspecialchars($alertData['alert_type']) . "</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Severity:</strong> <span style='color: {$severityStyles['header_bg']}; font-weight: bold;'>" . ucfirst(htmlspecialchars($severity)) . "</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Title:</strong> <span style='color: #333;'>" . htmlspecialchars($alertData['title']) . "</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Description:</strong> <span style='color: #333; line-height: 1.5;'>" . nl2br(htmlspecialchars($alertData['description'])) . "</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Location:</strong> <span style='color: #333;'>" . htmlspecialchars($location) . "</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Radius:</strong> <span style='color: #333;'>" . htmlspecialchars($alertData['radius_km']) . " km</span></li>
                        <li style='margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0;'><strong style='color: #555;'>Coordinates:</strong> <span style='color: #333; font-family: monospace;'>" . htmlspecialchars($coordinates) . "</span></li>
                        <li style='margin-bottom: 0;'><strong style='color: #555;'>Map:</strong> <a href='" . $mapsLink . "' class='map-link' target='_blank' style='color: #007bff; text-decoration: none; font-weight: bold;'>View on Google Maps →</a></li>
                    </ul>
                </div>
                
                <!-- Embedded Map Section -->
                <div style='margin-top: 20px; background-color: #fff; padding: 20px; border-radius: 5px; border: 1px solid #e0e0e0;'>
                    <h3 style='color: #333; margin-top: 0; font-size: 18px; border-bottom: 2px solid #f0f0f0; padding-bottom: 10px;'>📍 Alert Area Map</h3>
                    <div style='text-align: center; margin-bottom: 15px;'>
                        <div style='display: inline-block; background-color: {$severityStyles['header_bg']}; color: {$severityStyles['text_color']}; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: bold;'>
                            {$severityStyles['alert_icon']} Affected Area: " . htmlspecialchars($alertData['radius_km']) . " km radius
                        </div>
                    </div>
                    
                    <!-- Interactive Map Container -->
                    <div style='position: relative; height: 300px; border: 2px solid #dee2e6; border-radius: 5px; overflow: hidden; background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f8f9fa 75%), linear-gradient(-45deg, transparent 75%, #f8f9fa 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;'>
                        
                        <!-- Map Background -->
                        <div style='position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-color: #e9ecef;'>
                            <div style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;'>
                                <div style='background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 250px;'>
                                    <div style='font-size: 48px; margin-bottom: 15px;'>{$severityStyles['alert_icon']}</div>
                                    <div style='font-weight: bold; color: #333; margin-bottom: 8px; font-size: 16px;'>Alert Location</div>
                                    <div style='color: #666; font-size: 13px; line-height: 1.4; margin-bottom: 15px;'>" . htmlspecialchars($location) . "</div>
                                    
                                    <!-- Severity Badge -->
                                    <div style='padding: 8px 16px; background-color: {$severityStyles['header_bg']}; color: {$severityStyles['text_color']}; border-radius: 20px; font-size: 12px; font-weight: bold; margin-bottom: 10px;'>
                                        " . ucfirst(htmlspecialchars($severity)) . " Level Alert
                                    </div>
                                    
                                    <!-- Radius Info -->
                                    <div style='background: #f8f9fa; padding: 10px; border-radius: 8px; border-left: 4px solid {$severityStyles['border_color']};'>
                                        <div style='font-weight: bold; color: #333; margin-bottom: 5px;'>📏 Affected Area</div>
                                        <div style='color: {$severityStyles['header_bg']}; font-weight: bold; font-size: 16px;'>" . htmlspecialchars($alertData['radius_km']) . " km radius</div>
                                        <div style='font-size: 11px; color: #666;'>" . round(3.14159 * $alertData['radius_km'] * $alertData['radius_km'], 1) . " km² coverage area</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Coordinates Display -->
                        <div style='position: absolute; top: 10px; right: 10px; background: rgba(255,255,255,0.95); padding: 10px; border-radius: 8px; font-size: 11px; color: #333; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #dee2e6;'>
                            <div style='font-weight: bold; margin-bottom: 5px; color: #555;'>📍 Coordinates</div>
                            <div style='font-family: monospace; font-size: 10px; color: #666;'>
                                <div>Lat: " . number_format($alertData['latitude'], 6) . "</div>
                                <div>Lng: " . number_format($alertData['longitude'], 6) . "</div>
                            </div>
                        </div>
                        
                        <!-- Radius Circle Visualization -->
                        <div style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);'>
                            <div style='width: " . (min($alertData['radius_km'] * 15, 150)) . "px; height: " . (min($alertData['radius_km'] * 15, 150)) . "px; border: 3px solid {$severityStyles['header_bg']}; border-radius: 50%; background-color: {$severityStyles['header_bg']}; opacity: 0.2; animation: pulse 2s infinite;'></div>
                            <div style='position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 12px; height: 12px; background-color: {$severityStyles['header_bg']}; border: 3px solid white; border-radius: 50%; box-shadow: 0 2px 6px rgba(0,0,0,0.3);'></div>
                        </div>
                        
                        <!-- Map Attribution -->
                        <div style='position: absolute; bottom: 5px; left: 5px; background: rgba(255,255,255,0.8); padding: 3px 6px; border-radius: 3px; font-size: 9px; color: #666;'>
                            © OpenStreetMap contributors
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div style='margin-top: 15px; text-align: center;'>
                        <a href='" . $mapsLink . "' target='_blank' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; margin: 0 8px; box-shadow: 0 2px 4px rgba(0,123,255,0.3); transition: all 0.3s ease;'>
                            🗺️ View Full Map
                        </a>
                        <a href='https://www.google.com/maps/dir//" . $alertData['latitude'] . "," . $alertData['longitude'] . "' target='_blank' style='background-color: #28a745; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold; margin: 0 8px; box-shadow: 0 2px 4px rgba(40,167,69,0.3); transition: all 0.3s ease;'>
                            🚗 Get Directions
                        </a>
                    </div>
                    
                    <!-- CSS Animation for Pulse Effect -->
                    <style>
                        @keyframes pulse {
                            0% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
                            50% { transform: translate(-50%, -50%) scale(1.1); opacity: 0.3; }
                            100% { transform: translate(-50%, -50%) scale(1); opacity: 0.2; }
                        }
                    </style>
                </div>
                
                <div style='margin-top: 20px; padding: 20px; background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid {$severityStyles['border_color']};'>
                    <p style='margin: 0; color: #666; font-size: 14px; line-height: 1.5;'>{$severityStyles['alert_icon']} " . ($severity === 'emergency' ? 'Please take immediate action and follow emergency protocols. Stay safe and follow official instructions.' : ($severity === 'warning' ? 'Please take necessary precautions and follow safety protocols. Stay alert and follow official instructions.' : 'Please stay informed and follow any additional instructions from authorities.')) . "</p>
                </div>
            </div>
        ";

        // Build plain text body with severity-specific content
        $plainBody = "{$severityStyles['alert_icon']} " . strtoupper($severity) . " ALERT\n\n" .
                     "This is an automated alert from the university.\n\n" .
                     "ALERT DETAILS:\n" .
                     "=============\n" .
                     "Type: " . $alertData['alert_type'] . "\n" .
                     "Severity: " . ucfirst($severity) . "\n" .
                     "Title: " . $alertData['title'] . "\n" .
                     "Description: " . $alertData['description'] . "\n" .
                     "Location: " . $location . "\n" .
                     "Radius: " . $alertData['radius_km'] . " km\n" .
                     "Coordinates: " . $coordinates . "\n" .
                     "Map: " . $mapsLink . "\n\n" .
                     $severityStyles['alert_icon'] . " " . ($severity === 'emergency' ? 'Please take immediate action and follow emergency protocols. Stay safe and follow official instructions.' : ($severity === 'warning' ? 'Please take necessary precautions and follow safety protocols. Stay alert and follow official instructions.' : 'Please stay informed and follow any additional instructions from authorities.'));

        $mail->Body = $htmlBody;
        $mail->AltBody = $plainBody;

        error_log("Attempting to send email to: " . $userEmail);
        error_log("Email configuration: " . print_r([
            'host' => $mail->Host,
            'port' => $mail->Port,
            'username' => $mail->Username,
            'secure' => $mail->SMTPSecure,
            'debug_level' => $mail->SMTPDebug,
            'timeout' => $mail->Timeout
        ], true));

        $mail->send();
        error_log("Email sent successfully to: " . $userEmail);
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed to " . $userEmail . ": " . $e->getMessage());
        error_log("Full error details: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return false;
    }
}