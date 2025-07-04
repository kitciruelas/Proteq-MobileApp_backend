# Dart API Client - Fixing 401 Unauthorized Error

## Problem
You're getting a `401 (Unauthorized)` error when making a POST request to `/api/controller/IncidentReport.php?action=create` from your Dart client.

## Root Cause
The IncidentReport API endpoint requires authentication via a Bearer token in the Authorization header. Your Dart client is not including this token.

## Solution

### 1. Authentication Flow
The correct flow is:
1. **Login first** to get an authentication token
2. **Include the token** in subsequent API requests
3. **Use the token** in the Authorization header

### 2. Updated API Client
I've created a comprehensive `ApiClient` class in `lib/api/api_client.dart` that handles:

- ✅ Automatic token management
- ✅ Proper Authorization headers
- ✅ Login/logout functionality
- ✅ Session management
- ✅ Error handling

### 3. How to Use

#### Step 1: Login to get token
```dart
import 'api/api_client.dart';

// Login first
final loginResult = await ApiClient.login(
  email: 'your-email@example.com',
  password: 'your-password',
);

if (loginResult['success'] == true) {
  // Token is automatically stored
  print('Token: ${ApiClient.authToken}');
}
```

#### Step 2: Create incident report (now authenticated)
```dart
// This will automatically include the Authorization header
final result = await ApiClient.createIncidentReport(
  incidentType: 'fire',
  description: 'Fire in building A',
  longitude: 120.9842,
  latitude: 14.5995,
  priorityLevel: 'high',
  reporterSafeStatus: 'safe',
);
```

### 4. Key Changes Made

#### Before (causing 401 error):
```dart
// ❌ Missing Authorization header
final response = await http.post(
  Uri.parse('http://localhost/api/controller/IncidentReport.php'),
  body: jsonEncode(data),
  headers: {'Content-Type': 'application/json'},
);
```

#### After (working correctly):
```dart
// ✅ Includes Authorization header automatically
final response = await ApiClient.createIncidentReport(
  incidentType: 'fire',
  description: 'Fire detected',
  longitude: 120.9842,
  latitude: 14.5995,
);
```

### 5. Testing

Run the example to test:
```bash
dart run lib/example_usage.dart
```

### 6. Available Methods

- `ApiClient.login(email, password)` - Login and get token
- `ApiClient.createIncidentReport(...)` - Create incident report
- `ApiClient.checkSessionStatus()` - Check if session is valid
- `ApiClient.logout()` - Logout and clear token
- `ApiClient.authToken` - Get current token
- `ApiClient.setAuthToken(token)` - Manually set token
- `ApiClient.clearAuthToken()` - Clear token

### 7. Error Handling

The client now properly handles:
- ✅ Network errors
- ✅ Authentication errors (401)
- ✅ Validation errors (400)
- ✅ Server errors (500)

### 8. Integration with Flutter

For Flutter apps, use the `IncidentReportService` class:

```dart
class MyWidget extends StatefulWidget {
  @override
  _MyWidgetState createState() => _MyWidgetState();
}

class _MyWidgetState extends State<MyWidget> {
  Future<void> _submitReport() async {
    // Check if authenticated
    if (!IncidentReportService.isAuthenticated) {
      // Login first
      final loggedIn = await IncidentReportService.loginUser(
        'email@example.com',
        'password',
      );
      if (!loggedIn) return;
    }
    
    // Submit report
    final result = await IncidentReportService.submitIncidentReport(
      incidentType: 'fire',
      description: 'Fire detected',
      longitude: 120.9842,
      latitude: 14.5995,
    );
    
    if (result['success']) {
      print('Report submitted successfully!');
    } else {
      print('Error: ${result['message']}');
    }
  }
}
```

## Summary

The 401 error occurs because the API requires authentication. The solution is to:

1. **Always login first** to get a token
2. **Include the token** in the Authorization header
3. **Use the provided ApiClient** which handles this automatically

The updated `ApiClient` class will prevent this error by ensuring proper authentication flow. 