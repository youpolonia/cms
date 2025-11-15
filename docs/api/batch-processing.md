# Batch Processing API

## Overview
The Batch Processing API allows administrators to monitor and manage long-running batch operations in the CMS.

## Endpoints

### Get Batch Status
`GET /api/v1/batch`

Returns a list of all batches with their current status.

**Permissions Required**: `view_batches`

**Response**:
```json
{
  "data": [
    {
      "id": "string",
      "status": "pending|processing|completed|failed|cancelled",
      "progress": 0-100,
      "created_at": "datetime"
    }
  ]
}
```

### Get Batch Progress
`GET /api/v1/batch/{batch_id}/progress`

Returns detailed progress information for a specific batch.

**Permissions Required**: `view_batches`

**Response**:
```json
{
  "id": "string",
  "status": "string",
  "progress": 0-100,
  "total_items": "integer",
  "processed_items": "integer", 
  "error_count": "integer",
  "last_error": "string|null",
  "created_at": "datetime"
}
```

### Cancel Batch
`POST /api/v1/batch/{batch_id}/cancel`

Requests cancellation of a running batch.

**Permissions Required**: `manage_batches`

**Response**:
```json
{
  "success": "boolean",
  "message": "string"
}
```

## Error Responses
All endpoints return standard error responses:

- `401 Unauthorized` - Missing or invalid authentication
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Batch not found
- `500 Internal Server Error` - Unexpected server error