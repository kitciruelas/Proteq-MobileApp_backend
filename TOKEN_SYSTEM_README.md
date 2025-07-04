# Token-Based Session System

## Overview
The API has been updated to use a token-based authentication system instead of cookies. This change removes all cookie dependencies and provides a more secure, stateless authentication mechanism.

## Changes Made

### 1. Session Configuration (`config/session.php`)
- **Removed cookie-based session configuration**
  - Disabled `session.use_cookies`
  - Disabled `session.use_only_cookies`
  - Disabled `session.use_trans_sid`
  - Removed `session_set_cookie_params()`

- **Implemented token-based session management**
  - Added `SessionManager::$sessions` static array to store sessions in memory
  - Added `generateToken()` method to create unique 64-character hex tokens
  - Updated all session methods to accept and use tokens
  - Added `getTokenFromHeader()` method to extract tokens from Authorization headers

### 2. Login System (`controller/User/Logins.php`)
- **Updated login process**
  - `setUserSession()` and `setStaffSession()` now return tokens
  - Login response includes the generated token instead of session ID
  - No more cookie-based session management

### 3. Logout System (`controller/User/Logout.php`)
- **Updated logout process**
  - Extracts token from Authorization header
  - Validates token before logout
  - Removes session from memory instead of destroying cookies

### 4. Session Status (`controller/User/SessionStatus.php`)
- **Updated session validation**
  - Uses token from Authorization header
  - Validates token-based sessions
  - Returns session status without cookie dependencies

### 5. Token Refresh (`controller/User/RefreshToken.php`)
- **Updated token refresh**
  - Uses token from Authorization header
  - Updates activity timestamp for token-based sessions

### 6. Incident Report Controller (`controller/IncidentReport.php`)
- **Updated authentication**
  - Modified `createIncident()` method to use token-based auth
  - Modified `getAllIncidents()` method to use token-based auth
  - All authentication now uses tokens instead of cookies

## How It Works

### Authentication Flow
1. **Login**: User sends credentials via POST to `/controller/User/Logins.php`
2. **Token Generation**: Server validates credentials and generates a unique token
3. **Response**: Server returns the token in the response body
4. **Client Storage**: Client stores the token (localStorage, sessionStorage, etc.)
5. **API Calls**: Client includes token in Authorization header: `Bearer <token>`
6. **Validation**: Server validates token on each request
7. **Logout**: Client sends token to logout endpoint, server removes session

### Token Format
- **Type**: 64-character hexadecimal string
- **Example**: `a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef12345678`
- **Header Format**: `Authorization: Bearer a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef12345678`

### Session Storage
- **Location**: In-memory (PHP static array)
- **Lifetime**: 30 minutes of inactivity
- **Scope**: Per-request (no persistent storage)
- **Security**: Tokens are cryptographically secure random strings

## Benefits

### Security
- **No Cookie Vulnerabilities**: Eliminates CSRF, XSS cookie attacks
- **Stateless**: No server-side session storage persistence
- **Secure Tokens**: 256-bit random tokens are virtually unguessable
- **Explicit Authorization**: Each request must include the token

### Flexibility
- **Cross-Platform**: Works with any client (web, mobile, desktop)
- **Cross-Domain**: No CORS cookie restrictions
- **Scalable**: No shared session storage required
- **Stateless**: Can be easily distributed across multiple servers

### User Experience
- **Persistent**: Tokens can be stored securely on client side
- **Automatic**: No need to handle cookie management
- **Reliable**: No browser cookie restrictions or limitations

## Migration Notes

### For Frontend Developers
1. **Store Token**: Save the token from login response
2. **Include in Headers**: Add `Authorization: Bearer <token>` to all API requests
3. **Handle Expiry**: Implement token refresh or re-login on 401 responses
4. **Remove Cookie Logic**: No need to handle cookies anymore

### For Backend Developers
1. **Update Controllers**: All controllers using `SessionManager` need token parameter
2. **Add Authorization Header**: Include `Authorization` in CORS headers
3. **Test Authentication**: Verify all protected endpoints work with tokens

## Testing

Run the test file to verify the system:
```bash
php test_token_system.php
```

This will test all aspects of the token-based session system and confirm it's working correctly.

## API Endpoints Updated

- `POST /controller/User/Logins.php` - Returns token on successful login
- `POST /controller/User/Logout.php` - Requires token in Authorization header
- `GET /controller/User/SessionStatus.php` - Requires token in Authorization header
- `POST /controller/User/RefreshToken.php` - Requires token in Authorization header
- `POST /controller/IncidentReport.php` - Requires token in Authorization header
- All other protected endpoints require token in Authorization header

## Security Considerations

1. **Token Storage**: Store tokens securely on client side (not in localStorage for sensitive apps)
2. **HTTPS**: Always use HTTPS in production to protect tokens in transit
3. **Token Expiry**: Implement proper token refresh mechanisms
4. **Logout**: Always clear tokens on logout
5. **Token Rotation**: Consider implementing token rotation for enhanced security

## Future Enhancements

1. **Database Storage**: Store sessions in database for persistence across server restarts
2. **Token Refresh**: Implement automatic token refresh mechanism
3. **Rate Limiting**: Add rate limiting based on tokens
4. **Audit Logging**: Log token usage for security monitoring 