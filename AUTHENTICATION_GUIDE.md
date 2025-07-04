# Authentication Guide - Fixing 401 Unauthorized Error

## Problem
You're getting a `401 (Unauthorized)` error when trying to create an incident report from your Dart client. This happens because the incident report endpoint requires authentication, but your client isn't sending the proper authentication token.

## Root Cause
The `IncidentReport.php` endpoint requires users to be logged in. It checks for a valid authentication token in the `Authorization` header. Without this token, the request is rejected with a 401 error.

## Solution Steps

### 1. Authentication Flow
Your Dart client needs to follow this flow:

1. **Login first** to get an authentication token
2. **Store the token** in your client
3. **Include the token** in all subsequent requests that require authentication

### 2. Updated Dart Client Code

Here's how to fix your `api_client.dart`:

```dart
import 'dart:convert';
import 'dart:io';

class ApiClient {
  static const String baseUrl = 'http://localhost/api';
  static String? _authToken;

  // Set authentication token
  static void setAuthToken(String token) {
    _authToken = token;
  }

  // Clear authentication token
  static void clearAuthToken() {
    _authToken = null;
  }

  // Get current authentication token
  static String? get authToken => _authToken;

  // Login method - MUST be called before creating incident reports
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _makeRequest(
        endpoint: '/controller/User/Logins.php',
        method: 'POST',
        body: {'email': email, 'password': password},
        requiresAuth: false, // Login doesn't require auth
      );

      if (response['success'] == true && response['token'] != null) {
        setAuthToken(response['token']); // Store the token
        print('Login successful! Token stored.');
      }

      return response;
    } catch (e) {
      return {
        'success': false,
        'message': 'Login failed: ${e.toString()}',
      };
    }
  }

  // Create incident report (requires authentication)
  static Future<Map<String, dynamic>> createIncidentReport({
    required String incidentType,
    required String description,
    required double longitude,
    required double latitude,
    String? priorityLevel,
    String? reporterSafeStatus,
  }) async {
    // Check if user is logged in
    if (_authToken == null) {
      return {
        'success': false,
        'message': 'Authentication required. Please login first using ApiClient.login()',
      };
    }

    try {
      final response = await _makeRequest(
        endpoint: '/controller/IncidentReport.php',
        method: 'POST',
        body: {
          'incident_type': incidentType,
          'description': description,
          'longitude': longitude,
          'latitude': latitude,
          if (priorityLevel != null) 'priority_level': priorityLevel,
          if (reporterSafeStatus != null) 'reporter_safe_status': reporterSafeStatus,
        },
        requiresAuth: true, // This will include the Authorization header
      );

      return response;
    } catch (e) {
      return {
        'success': false,
        'message': 'Failed to create incident report: ${e.toString()}',
      };
    }
  }

  // Helper method to make HTTP requests
  static Future<Map<String, dynamic>> _makeRequest({
    required String endpoint,
    required String method,
    Map<String, dynamic>? body,
    bool requiresAuth = true,
  }) async {
    final url = Uri.parse('$baseUrl$endpoint');
    
    final headers = <String, String>{
      'Content-Type': 'application/json',
    };

    // Add Authorization header if token is available and auth is required
    if (requiresAuth && _authToken != null) {
      headers['Authorization'] = 'Bearer $_authToken';
      print('Adding Authorization header with token: ${_authToken!.substring(0, 20)}...');
    }

    final request = HttpClientRequest;
    HttpClientResponse response;

    try {
      final client = HttpClient();
      
      if (method == 'GET') {
        response = await client.getUrl(url).then((request) {
          headers.forEach((key, value) => request.headers.set(key, value));
          return request.close();
        });
      } else if (method == 'POST') {
        response = await client.postUrl(url).then((request) {
          headers.forEach((key, value) => request.headers.set(key, value));
          if (body != null) {
            request.write(jsonEncode(body));
          }
          return request.close();
        });
      } else {
        throw Exception('Unsupported HTTP method: $method');
      }

      final responseBody = await response.transform(utf8.decoder).join();
      
      print('Response status: ${response.statusCode}');
      print('Response body: $responseBody');
      
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return jsonDecode(responseBody);
      } else {
        // Try to parse error response
        try {
          final errorResponse = jsonDecode(responseBody);
          return errorResponse;
        } catch (e) {
          return {
            'success': false,
            'message': 'HTTP ${response.statusCode}: ${response.reasonPhrase}',
            'body': responseBody,
          };
        }
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Network error: ${e.toString()}',
      };
    }
  }
}
```

### 3. Usage Example

Here's how to use the updated client:

```dart
void main() async {
  // Step 1: Login first
  final loginResult = await ApiClient.login(
    email: 'your_email@example.com',
    password: 'your_password'
  );
  
  if (loginResult['success']) {
    print('Login successful!');
    
    // Step 2: Now you can create incident reports
    final incidentResult = await ApiClient.createIncidentReport(
      incidentType: 'fire',
      description: 'Test fire incident',
      longitude: 121.1564032,
      latitude: 14.0804096,
      priorityLevel: 'moderate',
      reporterSafeStatus: 'safe'
    );
    
    if (incidentResult['success']) {
      print('Incident report created successfully!');
    } else {
      print('Failed to create incident report: ${incidentResult['message']}');
    }
  } else {
    print('Login failed: ${loginResult['message']}');
  }
}
```

### 4. Testing Your Fix

1. **Use the HTML test file**: Open `test_incident_api.html` in your browser to test the complete flow
2. **Use the PHP test file**: Run `php test_incident_api.php` to test from command line
3. **Test your Dart client**: Make sure you call `login()` before `createIncidentReport()`

### 5. Common Issues and Solutions

#### Issue: "Authentication required" error
**Solution**: Make sure you call `ApiClient.login()` before trying to create incident reports.

#### Issue: "Session expired" error
**Solution**: The session times out after 30 minutes. Call `login()` again to get a new token.

#### Issue: "Invalid credentials" error
**Solution**: Check that you have valid user credentials in your database.

### 6. Database Setup

Make sure you have test users in your database. You can add a test user using this SQL:

```sql
INSERT INTO users (email, password, first_name, last_name, user_type, status) 
VALUES ('test@example.com', 'password123', 'Test', 'User', 'STUDENT', 1);
```

### 7. Debugging Tips

1. **Check the token**: Print the token after login to make sure it's not null
2. **Check headers**: Verify the Authorization header is being sent
3. **Check server logs**: Look at your PHP error logs for more details
4. **Use the test files**: The HTML and PHP test files will help you verify the API works

### 8. Complete Flow Summary

1. **Login** → Get token
2. **Store token** → Keep it in memory
3. **Send token** → Include in Authorization header for protected endpoints
4. **Handle expiration** → Re-login when session expires

This should resolve your 401 Unauthorized error. The key is ensuring you're logged in and sending the authentication token with your incident report requests. 