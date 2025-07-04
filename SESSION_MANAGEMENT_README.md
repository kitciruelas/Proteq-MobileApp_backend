# Session Management System

This document explains the session management system implemented for the Proteq API.

## Overview

The session management system provides secure, consistent session handling across all API endpoints. It includes:

- **SessionManager Class**: Centralized session management
- **Automatic session timeout**: 30-minute inactivity timeout
- **Secure session configuration**: HTTP-only cookies, secure settings
- **User and Staff session support**: Handles both user types consistently
- **Session validation**: Automatic authentication checks

## Files

### Core Files
- `config/session.php` - Main session management class
- `controller/User/Logins.php` - Updated login controller
- `controller/User/Logout.php` - New logout controller
- `controller/User/SessionStatus.php` - Session status checker

### Updated Files
- `model/User.php` - Removed manual session handling
- `model/Staff.php` - Removed manual session handling
- `controller/IncidentReport.php` - Updated to use SessionManager
- `model/IncidentReport.php` - Updated to use SessionManager

## SessionManager Class Methods

### Core Methods

#### `startSession()`
Starts a session if not already active.

#### `isLoggedIn()`
Returns `true` if user is logged in, `false` otherwise.

#### `getCurrentUserId()`
Returns the current user's ID (user_id or staff_id).

#### `getCurrentUserType()`
Returns the current user's type (user_type or role).

#### `getCurrentUserEmail()`
Returns the current user's email address.

#### `getCurrentUserName()`
Returns the current user's full name.

### Session Management

#### `setUserSession($userData)`
Sets session data for regular users.
```php
$userData = [
    'user_id' => 123,
    'user_type' => 'STUDENT',
    'email' => 'user@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'department' => 'Computer Science',
    'college' => 'Engineering',
    'status' => 1
];
SessionManager::setUserSession($userData);
```

#### `setStaffSession($staffData)`
Sets session data for staff members.
```php
$staffData = [
    'staff_id' => 456,
    'role' => 'SECURITY_OFFICER',
    'email' => 'security@example.com',
    'name' => 'Jane Smith',
    'availability' => 'ON_DUTY',
    'status' => 1
];
SessionManager::setStaffSession($staffData);
```

#### `logout()`
Destroys the current session and logs out the user.

#### `updateActivity()`
Updates the last activity timestamp to prevent session expiration.

### Authentication & Validation

#### `requireAuth($returnJson = true)`
Requires authentication for protected endpoints.
- If `$returnJson = true`: Returns JSON error and exits
- If `$returnJson = false`: Returns array with result

```php
// In a controller
$authResult = SessionManager::requireAuth(false);
if (!$authResult['success']) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => $authResult['message']]);
    return;
}
```

#### `isSessionExpired($timeoutMinutes = 30)`
Checks if the session has expired due to inactivity.

#### `getCurrentUserData()`
Returns all current user data as an array.

## API Endpoints

### Login
**POST** `/controller/User/Logins.php`
```json
{
    "email": "user@example.com",
    "password": "password123"
}
```

### Logout
**POST** `/controller/User/Logout.php`
No body required - uses session data.

### Session Status
**GET** `/controller/User/SessionStatus.php`
Returns current session information.

## Usage Examples

### In Controllers
```php
require_once __DIR__ . '/../config/session.php';

class MyController {
    public function protectedMethod() {
        // Require authentication
        SessionManager::requireAuth();
        
        // Get current user info
        $userId = SessionManager::getCurrentUserId();
        $userType = SessionManager::getCurrentUserType();
        
        // Your protected logic here
    }
}
```

### In Models
```php
require_once __DIR__ . '/../config/session.php';

class MyModel {
    public function someMethod() {
        // Get current user ID
        $userId = SessionManager::getCurrentUserId();
        
        // Check if user is logged in
        if (!SessionManager::isLoggedIn()) {
            throw new Exception("User not authenticated");
        }
        
        // Your model logic here
    }
}
```

## Session Configuration

The session is configured with the following security settings:

- **HTTP Only**: `session.cookie_httponly = 1`
- **Secure**: `session.cookie_secure = 0` (set to 1 for HTTPS)
- **SameSite**: `session.cookie_samesite = 'Strict'`
- **Timeout**: 30 minutes of inactivity
- **Cookie Only**: `session.use_only_cookies = 1`

## Testing

Run the test file to verify session management:
```
http://localhost/   .php
```

This will test all session management functions and display the results.

## Mobile App Integration

For the Flutter mobile app, the session management works as follows:

1. **Login**: Call the login endpoint, session is automatically created
2. **Session Check**: Call session status endpoint to verify session is valid
3. **API Calls**: All protected endpoints automatically check session
4. **Logout**: Call logout endpoint to destroy session

### Example Flutter Usage
```dart
// Login
final response = await http.post(
  Uri.parse('http://localhost/controller/User/Logins.php'),
  headers: {'Content-Type': 'application/json'},
  body: jsonEncode({
    'email': 'user@example.com',
    'password': 'password123'
  })
);

// Check session status
final statusResponse = await http.get(
  Uri.parse('http://localhost/controller/User/SessionStatus.php')
);

// Logout
final logoutResponse = await http.post(
  Uri.parse('http://localhost/controller/User/Logout.php')
);
```

## Security Features

1. **Automatic Session Timeout**: Sessions expire after 30 minutes of inactivity
2. **Secure Cookie Settings**: HTTP-only, secure, and SameSite cookies
3. **Session Validation**: All protected endpoints validate session status
4. **Proper Logout**: Complete session destruction on logout
5. **Activity Tracking**: Last activity timestamp prevents premature expiration

## Troubleshooting

### Common Issues

1. **Session not persisting**: Check cookie settings and domain configuration
2. **Premature logout**: Verify session timeout settings
3. **Authentication errors**: Ensure SessionManager is included in all files
4. **CORS issues**: Check Access-Control-Allow-Origin headers

### Debug Mode

To debug session issues, you can temporarily add logging:
```php
error_log("Session data: " . print_r($_SESSION, true));
```

## Migration Notes

If you're updating from the old session system:

1. Remove manual `session_start()` calls
2. Replace `$_SESSION` access with SessionManager methods
3. Update authentication checks to use `SessionManager::requireAuth()`
4. Test all endpoints to ensure proper session handling

The new system provides better security, consistency, and maintainability compared to the previous manual session handling. 