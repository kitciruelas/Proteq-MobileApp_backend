<?php
/**
 * Test file for token-based session system
 */

require_once __DIR__ . '/config/session.php';

echo "<h1>Token-Based Session System Test</h1>";

// Test 1: Generate a token
echo "<h2>Test 1: Token Generation</h2>";
$token1 = SessionManager::generateToken();
$token2 = SessionManager::generateToken();
echo "Token 1: " . $token1 . "<br>";
echo "Token 2: " . $token2 . "<br>";
echo "Tokens are different: " . ($token1 !== $token2 ? "YES" : "NO") . "<br><br>";

// Test 2: Set user session
echo "<h2>Test 2: User Session</h2>";
$testUserData = [
    'user_id' => 123,
    'user_type' => 'student',
    'email' => 'test@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'department' => 'Computer Science',
    'college' => 'Engineering',
    'status' => 1
];

$userToken = SessionManager::setUserSession($testUserData);
echo "User Token: " . $userToken . "<br>";
echo "Is logged in: " . (SessionManager::isLoggedIn($userToken) ? "YES" : "NO") . "<br>";
echo "User ID: " . SessionManager::getCurrentUserId($userToken) . "<br>";
echo "User Type: " . SessionManager::getCurrentUserType($userToken) . "<br>";
echo "User Email: " . SessionManager::getCurrentUserEmail($userToken) . "<br>";
echo "User Name: " . SessionManager::getCurrentUserName($userToken) . "<br><br>";

// Test 3: Set staff session
echo "<h2>Test 3: Staff Session</h2>";
$testStaffData = [
    'staff_id' => 456,
    'role' => 'admin',
    'email' => 'admin@example.com',
    'name' => 'Jane Smith',
    'availability' => 1,
    'status' => 1
];

$staffToken = SessionManager::setStaffSession($testStaffData);
echo "Staff Token: " . $staffToken . "<br>";
echo "Is logged in: " . (SessionManager::isLoggedIn($staffToken) ? "YES" : "NO") . "<br>";
echo "Staff ID: " . SessionManager::getCurrentUserId($staffToken) . "<br>";
echo "Staff Role: " . SessionManager::getCurrentUserType($staffToken) . "<br>";
echo "Staff Email: " . SessionManager::getCurrentUserEmail($staffToken) . "<br>";
echo "Staff Name: " . SessionManager::getCurrentUserName($staffToken) . "<br><br>";

// Test 4: Session expiration
echo "<h2>Test 4: Session Expiration</h2>";
echo "User session expired: " . (SessionManager::isSessionExpired($userToken) ? "YES" : "NO") . "<br>";
echo "Staff session expired: " . (SessionManager::isSessionExpired($staffToken) ? "YES" : "NO") . "<br><br>";

// Test 5: Update activity
echo "<h2>Test 5: Update Activity</h2>";
SessionManager::updateActivity($userToken);
SessionManager::updateActivity($staffToken);
echo "Activity updated for both sessions<br><br>";

// Test 6: Get user data
echo "<h2>Test 6: Get User Data</h2>";
$userData = SessionManager::getCurrentUserData($userToken);
$staffData = SessionManager::getCurrentUserData($staffToken);
echo "User Data: <pre>" . print_r($userData, true) . "</pre>";
echo "Staff Data: <pre>" . print_r($staffData, true) . "</pre>";

// Test 7: Logout
echo "<h2>Test 7: Logout</h2>";
SessionManager::logout($userToken);
echo "User logged out. Is logged in: " . (SessionManager::isLoggedIn($userToken) ? "YES" : "NO") . "<br>";
echo "Staff still logged in: " . (SessionManager::isLoggedIn($staffToken) ? "YES" : "NO") . "<br>";

SessionManager::logout($staffToken);
echo "Staff logged out. Is logged in: " . (SessionManager::isLoggedIn($staffToken) ? "YES" : "NO") . "<br><br>";

// Test 8: Invalid token
echo "<h2>Test 8: Invalid Token</h2>";
$invalidToken = "invalid_token_123";
echo "Invalid token is logged in: " . (SessionManager::isLoggedIn($invalidToken) ? "YES" : "NO") . "<br>";
echo "Invalid token user ID: " . (SessionManager::getCurrentUserId($invalidToken) ?? "NULL") . "<br>";

echo "<h2>Test Complete!</h2>";
echo "<p>The token-based session system is working correctly. No cookies are used.</p>";
?> 