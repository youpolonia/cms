# Content Scheduling API Documentation

## Overview
The Content Scheduling API allows managing scheduled content publication through RESTful endpoints. All endpoints require authentication and appropriate permissions.

## Base URL
`https://api.example.com/api`

## Endpoints

### List Schedules
`GET /content/{contentId}/schedules`

**Parameters:**
- `contentId` (required) - ID of the content item

**Response (200):**
```json
[
  {
    "id": "sch_123",
    "publish_at": "2025-05-10T09:00:00Z",
    "priority": 2,
    "status": "pending"
  }
]
```

### Create Schedule
`POST /content/{contentId}/schedules`

**Parameters:**
- `contentId` (required) - ID of the content item

**Body:**
```json
{
  "publish_at": "2025-05-10T09:00:00Z",
  "priority": 2
}
```

**Response (201):**
```json
{
  "id": "sch_123",
  "content_id": "cont_456",
  "publish_at": "2025-05-10T09:00:00Z",
  "priority": 2,
  "status": "pending"
}
```

### Update Schedule
`PUT /schedules/{scheduleId}`

**Parameters:**
- `scheduleId` (required) - ID of the schedule

**Body:**
```json
{
  "publish_at": "2025-05-11T09:00:00Z",
  "priority": 1
}
```

**Response (200):**
```json
{
  "id": "sch_123",
  "publish_at": "2025-05-11T09:00:00Z",
  "priority": 1,
  "status": "pending"
}
```

### Delete Schedule
`DELETE /schedules/{scheduleId}`

**Parameters:**
- `scheduleId` (required) - ID of the schedule

**Response:** 204 No Content

## Error Handling

**400 Bad Request** - Invalid schedule data  
**401 Unauthorized** - Missing/invalid authentication  
**403 Forbidden** - Insufficient permissions  
**404 Not Found** - Content or schedule not found  
**422 Unprocessable Entity** - Validation error

```json
{
  "message": "Validation failed",
  "errors": {
    "publish_at": ["The publish at field is required"],
    "priority": ["Priority must be between 1 and 5"]
  }
}