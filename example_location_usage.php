<?php
/**
 * Example usage of StaffLocation model and controller
 * 
 * This file demonstrates how to use the location update functionality
 * that has been refactored into the MVC pattern.
 */

// Example 1: Using the model directly
require_once 'model/StaffLocation.php';

$locationModel = new StaffLocation();

// Update location
$result = $locationModel->updateLocation(1, 14.5995, 120.9842); // Manila coordinates
if ($result['success']) {
    echo "Location updated successfully: " . $result['message'] . "\n";
} else {
    echo "Error: " . $result['message'] . "\n";
}

// Get location
$location = $locationModel->getLocation(1);
if ($location) {
    echo "Staff location: " . $location['latitude'] . ", " . $location['longitude'] . "\n";
} else {
    echo "No location found\n";
}

// Get all locations (for admin)
$allLocations = $locationModel->getAllLocations();
if ($allLocations['success']) {
    echo "Total staff locations: " . count($allLocations['data']) . "\n";
} else {
    echo "Error fetching locations: " . $allLocations['message'] . "\n";
}

echo "\n--- API Endpoints ---\n";
echo "1. Update location: POST /controller/StaffLocation.php\n";
echo "   Body: {\"latitude\": 14.5995, \"longitude\": 120.9842}\n\n";

echo "2. Get current location: GET /controller/StaffLocation.php\n\n";

echo "3. Get all locations (admin): GET /controller/StaffLocation.php?action=all\n\n";

echo "4. Delete location: DELETE /controller/StaffLocation.php\n\n";

echo "Authentication:\n";
echo "- Session-based: Uses \$_SESSION['staff_id']\n";
echo "- Token-based: Uses Authorization header with Bearer token\n";
?> 