import 'api/api_client.dart';

void main() async {
  // Example usage of the API client
  
  print('=== Proteq API Client Example ===\n');
  
  // Step 1: Login to get authentication token
  print('1. Logging in...');
  final loginResult = await ApiClient.login(
    email: 'test@example.com',
    password: 'password123',
  );
  
  if (loginResult['success'] == true) {
    print('✅ Login successful!');
    print('Token: ${ApiClient.authToken}');
    print('User: ${loginResult['user']}\n');
    
    // Step 2: Create an incident report (now authenticated)
    print('2. Creating incident report...');
    final incidentResult = await ApiClient.createIncidentReport(
      incidentType: 'fire',
      description: 'Fire detected in Building A, 3rd floor',
      longitude: 120.9842,
      latitude: 14.5995,
      priorityLevel: 'high',
      reporterSafeStatus: 'safe',
    );
    
    if (incidentResult['success'] == true) {
      print('✅ Incident report created successfully!');
      print('Incident ID: ${incidentResult['incident_id']}');
      print('Reported by: ${incidentResult['reported_by']}\n');
    } else {
      print('❌ Failed to create incident report:');
      print('Error: ${incidentResult['message']}\n');
    }
    
    // Step 3: Check session status
    print('3. Checking session status...');
    final sessionResult = await ApiClient.checkSessionStatus();
    if (sessionResult['success'] == true) {
      print('✅ Session is valid');
      print('User data: ${sessionResult['user']}\n');
    } else {
      print('❌ Session check failed: ${sessionResult['message']}\n');
    }
    
    // Step 4: Logout
    print('4. Logging out...');
    final logoutResult = await ApiClient.logout();
    if (logoutResult['success'] == true) {
      print('✅ Logout successful!\n');
    } else {
      print('❌ Logout failed: ${logoutResult['message']}\n');
    }
    
  } else {
    print('❌ Login failed:');
    print('Error: ${loginResult['message']}\n');
    
    // Try to create incident report without authentication (should fail)
    print('2. Attempting to create incident report without authentication...');
    final incidentResult = await ApiClient.createIncidentReport(
      incidentType: 'fire',
      description: 'This should fail',
      longitude: 120.9842,
      latitude: 14.5995,
    );
    
    if (incidentResult['success'] == false) {
      print('✅ Correctly rejected unauthenticated request');
      print('Error: ${incidentResult['message']}\n');
    }
  }
}

// Example of how to handle the API client in a Flutter app
class IncidentReportService {
  static Future<bool> loginUser(String email, String password) async {
    final result = await ApiClient.login(email: email, password: password);
    return result['success'] == true;
  }
  
  static Future<Map<String, dynamic>> submitIncidentReport({
    required String incidentType,
    required String description,
    required double longitude,
    required double latitude,
    String? priorityLevel,
    String? reporterSafeStatus,
  }) async {
    return await ApiClient.createIncidentReport(
      incidentType: incidentType,
      description: description,
      longitude: longitude,
      latitude: latitude,
      priorityLevel: priorityLevel,
      reporterSafeStatus: reporterSafeStatus,
    );
  }
  
  static Future<void> logoutUser() async {
    await ApiClient.logout();
  }
  
  static bool get isAuthenticated => ApiClient.authToken != null;
} 