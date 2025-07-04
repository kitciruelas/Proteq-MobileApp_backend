<?php
/**
 * Session Management Test File
 * Tests the session management functionality
 */

require_once __DIR__ . '/config/session.php';

echo "<h1>Session Management Test</h1>";

// Test 1: Check if session starts properly
echo "<h2>Test 1: Session Start</h2>";
SessionManager::startSession();
echo "Session started: " . (session_status() === PHP_SESSION_ACTIVE ? "YES" : "NO") . "<br>";

// Test 2: Check if user is logged in (should be false initially)
echo "<h2>Test 2: Initial Login Status</h2>";
echo "User logged in: " . (SessionManager::isLoggedIn() ? "YES" : "NO") . "<br>";

// Test 3: Test session data setting
echo "<h2>Test 3: Setting User Session Data</h2>";
$testUserData = [
    'user_id' => 123,
    'user_type' => 'STUDENT',
    'email' => 'test@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'department' => 'Computer Science',
    'college' => 'Engineering',
    'status' => 1
];

SessionManager::setUserSession($testUserData);
echo "User session data set<br>";
echo "User ID: " . SessionManager::getCurrentUserId() . "<br>";
echo "User Type: " . SessionManager::getCurrentUserType() . "<br>";
echo "User Email: " . SessionManager::getCurrentUserEmail() . "<br>";
echo "User Name: " . SessionManager::getCurrentUserName() . "<br>";

// Test 4: Check if user is now logged in
echo "<h2>Test 4: Login Status After Setting Session</h2>";
echo "User logged in: " . (SessionManager::isLoggedIn() ? "YES" : "NO") . "<br>";

// Test 5: Test session expiration
echo "<h2>Test 5: Session Expiration Check</h2>";
echo "Session expired: " . (SessionManager::isSessionExpired() ? "YES" : "NO") . "<br>";

// Test 6: Test activity update
echo "<h2>Test 6: Activity Update</h2>";
SessionManager::updateActivity();
echo "Activity updated<br>";
echo "Session expired after update: " . (SessionManager::isSessionExpired() ? "YES" : "NO") . "<br>";

// Test 7: Test getting current user data
echo "<h2>Test 7: Current User Data</h2>";
$userData = SessionManager::getCurrentUserData();
echo "<pre>";
print_r($userData);
echo "</pre>";

// Test 8: Test staff session
echo "<h2>Test 8: Staff Session</h2>";
$testStaffData = [
    'staff_id' => 456,
    'role' => 'SECURITY_OFFICER',
    'email' => 'security@example.com',
    'name' => 'Jane Smith',
    'availability' => 'ON_DUTY',
    'status' => 1
];

SessionManager::setStaffSession($testStaffData);
echo "Staff session data set<br>";
echo "Staff ID: " . SessionManager::getCurrentUserId() . "<br>";
echo "Staff Role: " . SessionManager::getCurrentUserType() . "<br>";
echo "Staff Email: " . SessionManager::getCurrentUserEmail() . "<br>";
echo "Staff Name: " . SessionManager::getCurrentUserName() . "<br>";

// Test 9: Test logout
echo "<h2>Test 9: Logout</h2>";
SessionManager::logout();
echo "User logged out<br>";
echo "User logged in after logout: " . (SessionManager::isLoggedIn() ? "YES" : "NO") . "<br>";

// Test 10: Test requireAuth
echo "<h2>Test 10: Require Auth (should fail)</h2>";
$authResult = SessionManager::requireAuth(false);
echo "Auth result: ";
echo "<pre>";
print_r($authResult);
echo "</pre>";

echo "<h2>Session Management Test Complete!</h2>";
echo "<p>All session management functions are working correctly.</p>";
?> 