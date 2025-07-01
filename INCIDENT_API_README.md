# Incident Report API Documentation

This API provides endpoints for managing incident reports in the emergency response system.

## Base URL
```
http://localhost/api/controller/IncidentReport.php
```

## URL Format
The API uses query parameters to specify actions:
- `?action=create` - Create new incident
- `?action=get_all` - Get all incidents
- `?action=get_by_id&id=1` - Get incident by ID
- `?action=update&id=1` - Update incident
- `?action=delete&id=1` - Delete incident
- `?action=update_status&id=1` - Update incident status
- `?action=validate&id=1` - Validate incident
- `?action=assign&id=1` - Assign incident to staff
- `?action=get_by_user&user_id=1` - Get incidents by user
- `?action=stats` - Get incident statistics

## Authentication
Currently, the API doesn't require authentication tokens, but user validation should be implemented in production.

## Endpoints

### 1. Create Incident Report
**POST** `?action=create`

Creates a new incident report.

**Request Body:**
```json
{
    "incident_type": "fire",
    "description": "Fire in building A",
    "longitude": 121.0299469,
    "latitude": 14.6044363,
    "reported_by": 1,
    "priority_level": "high",
    "reporter_safe_status": "safe"
}
```

**Required Fields:**
- `incident_type`: Type of incident (fire, earthquake, flood, typhoon, medical, security, other)
- `description`: Description of the incident
- `longitude`: Longitude coordinate (decimal)
- `latitude`: Latitude coordinate (decimal)
- `reported_by`: User ID of the person reporting

**Optional Fields:**
- `priority_level`: low, moderate, high, critical (default: moderate)
- `reporter_safe_status`: safe, injured, unknown (default: unknown)

**Response:**
```json
{
    "success": true,
    "message": "Incident report created successfully",
    "incident_id": 123
}
```

### 2. Get All Incidents
**GET** `?action=get_all`

Retrieves all incident reports with optional filtering.

**Query Parameters:**
- `status`: Filter by status (pending, in_progress, resolved, closed)
- `incident_type`: Filter by incident type
- `validation_status`: Filter by validation status (unvalidated, validated, rejected)
- `priority_level`: Filter by priority level

**Example:**
```
GET ?action=get_all&status=pending&priority_level=high
```

**Response:**
```json
{
    "success": true,
    "message": "Incidents retrieved successfully",
    "data": [
        {
            "incident_id": 1,
            "incident_type": "fire",
            "description": "Fire in building A",
            "longitude": "121.0299469",
            "latitude": "14.6044363",
            "date_reported": "2025-06-11 10:30:00",
            "status": "pending",
            "assigned_to": null,
            "reported_by": 1,
            "validation_status": "unvalidated",
            "validation_notes": null,
            "priority_level": "high",
            "reporter_safe_status": "safe",
            "created_at": "2025-06-11 10:30:00",
            "updated_at": "2025-06-11 10:30:00",
            "reporter_first_name": "John",
            "reporter_last_name": "Doe",
            "reporter_email": "john@example.com",
            "assigned_staff_name": null,
            "assigned_staff_role": null
        }
    ],
    "count": 1
}
```

### 3. Get Incident by ID
**GET** `?action=get_by_id&id={id}`

Retrieves a specific incident report by ID.

**Response:**
```json
{
    "success": true,
    "message": "Incident retrieved successfully",
    "data": {
        "incident_id": 1,
        "incident_type": "fire",
        "description": "Fire in building A",
        "longitude": "121.0299469",
        "latitude": "14.6044363",
        "date_reported": "2025-06-11 10:30:00",
        "status": "pending",
        "assigned_to": null,
        "reported_by": 1,
        "validation_status": "unvalidated",
        "validation_notes": null,
        "priority_level": "high",
        "reporter_safe_status": "safe",
        "created_at": "2025-06-11 10:30:00",
        "updated_at": "2025-06-11 10:30:00",
        "reporter_first_name": "John",
        "reporter_last_name": "Doe",
        "reporter_email": "john@example.com",
        "assigned_staff_name": null,
        "assigned_staff_role": null
    }
}
```

### 4. Update Incident Report
**PUT** `?action=update&id={id}`

Updates an existing incident report.

**Request Body:**
```json
{
    "incident_type": "fire",
    "description": "Updated description",
    "longitude": 121.0299469,
    "latitude": 14.6044363,
    "priority_level": "critical",
    "reporter_safe_status": "injured"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Incident updated successfully"
}
```

### 5. Delete Incident Report
**DELETE** `?action=delete&id={id}`

Deletes an incident report.

**Response:**
```json
{
    "success": true,
    "message": "Incident deleted successfully"
}
```

### 6. Update Incident Status
**PUT** `?action=update_status&id={id}`

Updates the status of an incident report.

**Request Body:**
```json
{
    "status": "in_progress"
}
```

**Valid Status Values:**
- `pending`
- `in_progress`
- `resolved`
- `closed`

**Response:**
```json
{
    "success": true,
    "message": "Incident status updated successfully"
}
```

### 7. Validate Incident Report
**PUT** `?action=validate&id={id}`

Validates or rejects an incident report.

**Request Body:**
```json
{
    "validation_status": "validated",
    "validation_notes": "Confirmed by security team"
}
```

**Valid Validation Status Values:**
- `unvalidated`
- `validated`
- `rejected`

**Response:**
```json
{
    "success": true,
    "message": "Incident validation updated successfully"
}
```

### 8. Assign Incident to Staff
**PUT** `?action=assign&id={id}`

Assigns an incident to a staff member.

**Request Body:**
```json
{
    "staff_id": 1
}
```

**Response:**
```json
{
    "success": true,
    "message": "Incident assigned successfully"
}
```

### 9. Get Incidents by User
**GET** `?action=get_by_user&user_id={user_id}`

Retrieves all incidents reported by a specific user.

**Response:**
```json
{
    "success": true,
    "message": "User incidents retrieved successfully",
    "data": [
        {
            "incident_id": 1,
            "incident_type": "fire",
            "description": "Fire in building A",
            "longitude": "121.0299469",
            "latitude": "14.6044363",
            "date_reported": "2025-06-11 10:30:00",
            "status": "pending",
            "assigned_to": null,
            "reported_by": 1,
            "validation_status": "unvalidated",
            "validation_notes": null,
            "priority_level": "high",
            "reporter_safe_status": "safe",
            "created_at": "2025-06-11 10:30:00",
            "updated_at": "2025-06-11 10:30:00",
            "assigned_staff_name": null,
            "assigned_staff_role": null
        }
    ],
    "count": 1
}
```

### 10. Get Incident Statistics
**GET** `?action=stats`

Retrieves statistics about incident reports.

**Response:**
```json
{
    "success": true,
    "message": "Statistics retrieved successfully",
    "data": {
        "total": 25,
        "by_status": {
            "pending": 10,
            "in_progress": 5,
            "resolved": 8,
            "closed": 2
        },
        "by_type": {
            "fire": 8,
            "earthquake": 5,
            "flood": 3,
            "typhoon": 4,
            "medical": 2,
            "security": 2,
            "other": 1
        },
        "by_priority": {
            "low": 5,
            "moderate": 12,
            "high": 6,
            "critical": 2
        },
        "recent_7_days": 8
    }
}
```

## Error Responses

All endpoints return error responses in the following format:

```json
{
    "success": false,
    "message": "Error description"
}
```

**Common HTTP Status Codes:**
- `200`: Success
- `201`: Created (for POST requests)
- `400`: Bad Request (validation errors)
- `404`: Not Found
- `405`: Method Not Allowed

## Database Schema

The incident reports are stored in the `incident_reports` table with the following structure:

```sql
CREATE TABLE `incident_reports` (
  `incident_id` int(11) NOT NULL AUTO_INCREMENT,
  `incident_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `longitude` decimal(10,7) NOT NULL,
  `latitude` decimal(10,7) NOT NULL,
  `date_reported` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','in_progress','resolved','closed') NOT NULL DEFAULT 'pending',
  `assigned_to` int(11) DEFAULT NULL,
  `reported_by` int(11) NOT NULL,
  `validation_status` enum('unvalidated','validated','rejected') NOT NULL DEFAULT 'unvalidated',
  `validation_notes` text DEFAULT NULL,
  `priority_level` enum('low','moderate','high','critical') NOT NULL DEFAULT 'moderate',
  `reporter_safe_status` enum('safe','injured','unknown') NOT NULL DEFAULT 'unknown',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`incident_id`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `incident_reports_ibfk_1` FOREIGN KEY (`assigned_to`) REFERENCES `staff` (`staff_id`) ON DELETE SET NULL
);
```

## Testing

You can use the provided `incident_test.html` file to test all the API endpoints. Simply open the file in a web browser and use the forms to interact with the API.

## Notes

1. All timestamps are in the server's timezone
2. Coordinates should be provided as decimal degrees
3. User IDs and Staff IDs must exist in their respective tables
4. The API includes proper validation for all input fields
5. Foreign key constraints ensure data integrity 