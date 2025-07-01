<?php
// Simple API test file
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Test database connection
try {
    require_once __DIR__ . '/config/db.php';
    
    $testQuery = "SELECT COUNT(*) as count FROM incident_reports";
    $testResult = $conn->query($testQuery);
    
    if ($testResult) {
        $count = $testResult->fetch_assoc()['count'];
        $dbStatus = "Connected - Found $count incident reports";
    } else {
        $dbStatus = "Connected but query failed";
    }
} catch (Exception $e) {
    $dbStatus = "Database error: " . $e->getMessage();
}

// Test model loading
try {
    require_once __DIR__ . '/model/IncidentReport.php';
    $modelStatus = "Model loaded successfully";
} catch (Exception $e) {
    $modelStatus = "Model error: " . $e->getMessage();
}

echo json_encode([
    'status' => 'API is accessible',
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'request_uri' => $_SERVER['REQUEST_URI'],
    'database_status' => $dbStatus,
    'model_status' => $modelStatus,
    'php_version' => PHP_VERSION,
    'available_endpoints' => [
        'GET /api/controller/IncidentReport.php?action=get_all' => 'Get all incidents',
        'POST /api/controller/IncidentReport.php?action=create' => 'Create incident',
        'GET /api/controller/IncidentReport.php?action=get_by_id&id=1' => 'Get incident by ID',
        'PUT /api/controller/IncidentReport.php?action=update_status&id=1' => 'Update incident status',
        'GET /api/controller/IncidentReport.php?action=stats' => 'Get statistics'
    ]
]);
?> 