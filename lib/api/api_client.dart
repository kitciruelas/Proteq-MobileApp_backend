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

  // Login method for PHP backend
  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    try {
      final response = await _makeRequest(
        endpoint: '/controller/User/Logins.php',
        method: 'POST',
        body: {'email': email, 'password': password},
        requiresAuth: false,
      );

      if (response['success'] == true && response['token'] != null) {
        setAuthToken(response['token']);
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
    if (_authToken == null) {
      return {
        'success': false,
        'message': 'Authentication required. Please login first.',
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
        requiresAuth: true,
      );

      return response;
    } catch (e) {
      return {
        'success': false,
        'message': 'Failed to create incident report: ${e.toString()}',
      };
    }
  }

  // Logout method
  static Future<Map<String, dynamic>> logout() async {
    try {
      final response = await _makeRequest(
        endpoint: '/controller/User/Logout.php',
        method: 'POST',
        requiresAuth: true,
      );

      if (response['success'] == true) {
        clearAuthToken();
      }

      return response;
    } catch (e) {
      return {
        'success': false,
        'message': 'Logout failed: ${e.toString()}',
      };
    }
  }

  // Check session status
  static Future<Map<String, dynamic>> checkSessionStatus() async {
    try {
      return await _makeRequest(
        endpoint: '/controller/User/SessionStatus.php',
        method: 'GET',
        requiresAuth: true,
      );
    } catch (e) {
      return {
        'success': false,
        'message': 'Failed to check session status: ${e.toString()}',
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