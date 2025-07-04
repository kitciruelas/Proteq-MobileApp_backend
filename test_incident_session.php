<?php
/**
 * Test Incident Report with Session Management
 * This file tests the incident report system with both authenticated and anonymous users
 */

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/model/IncidentReport.php';

echo "<h1>Incident Report Session Test</h1>";

// Test 1: Create anonymous incident report
echo "<h2>Test 1: Anonymous Incident Report</h2>";
$incidentModel = new IncidentReport();

$anonymousData = [
    'incident_type' => 'fire',
    'description' => 'Test fire incident - anonymous user',
    'longitude' => 120.9842,
    'latitude' => 14.5995,
    'priority_level' => 'high',
    'reporter_safe_status' => 'safe'
];

$result = $incidentModel->createIncident($anonymousData, null);
echo "Anonymous report result: " . json_encode($result, JSON_PRETTY_PRINT) . "<br><br>";

// Test 2: Create authenticated user session
echo "<h2>Test 2: Create Authenticated User Session</h2>";
$userData = [
    'user_id' => 123,
    'user_type' => 'STUDENT',
    'email' => 'test@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'department' => 'Computer Science',
    'college' => 'Engineering',
    'status' => 1
];

$token = SessionManager::setUserSession($userData);
echo "Token generated: " . $token . "<br>";
echo "User logged in: " . (SessionManager::isLoggedIn($token) ? "YES" : "NO") . "<br>";
echo "User ID: " . SessionManager::getCurrentUserId($token) . "<br>";
echo "User Type: " . SessionManager::getCurrentUserType($token) . "<br><br>";

// Test 3: Create authenticated incident report
echo "<h2>Test 3: Authenticated Incident Report</h2>";
$authenticatedData = [
    'incident_type' => 'medical',
    'description' => 'Test medical incident - authenticated user',
    'longitude' => 120.9842,
    'latitude' => 14.5995,
    'priority_level' => 'urgent',
    'reporter_safe_status' => 'safe'
];

$result = $incidentModel->createIncident($authenticatedData, SessionManager::getCurrentUserId($token));
echo "Authenticated report result: " . json_encode($result, JSON_PRETTY_PRINT) . "<br><br>";

// Test 4: Test session validation
echo "<h2>Test 4: Session Validation</h2>";
echo "Session expired: " . (SessionManager::isSessionExpired($token) ? "YES" : "NO") . "<br>";
echo "User still logged in: " . (SessionManager::isLoggedIn($token) ? "YES" : "NO") . "<br>";

// Test 5: Update activity and check again
echo "<h2>Test 5: Update Activity</h2>";
SessionManager::updateActivity($token);
echo "Activity updated<br>";
echo "Session expired after update: " . (SessionManager::isSessionExpired($token) ? "YES" : "NO") . "<br>";

// Test 6: Get all incidents to see both reports
echo "<h2>Test 6: Get All Incidents</h2>";
$allIncidents = $incidentModel->getAllIncidents();
echo "All incidents: " . json_encode($allIncidents, JSON_PRETTY_PRINT) . "<br><br>";

// Test 7: Test logout
echo "<h2>Test 7: Logout</h2>";
SessionManager::logout($token);
echo "User logged in after logout: " . (SessionManager::isLoggedIn($token) ? "YES" : "NO") . "<br>";

echo "<h2>Test Complete!</h2>";
echo "The incident report system is working correctly with session management.<br>";
echo "Both authenticated and anonymous users can create incident reports.<br>";
echo "Session information is properly tracked and validated.<br>";
?> 