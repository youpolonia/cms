# Version Control API Documentation

## Overview
The Version Control API manages content versioning, including:
- Creating new versions
- Comparing versions
- Rolling back to previous versions
- Managing version approval workflows

## Authentication
- Requires valid API token with `version_manager` role
- Token must be passed in `Authorization` header

## Rate Limits
- 100 requests per minute per tenant
- Responses include `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

## Endpoints

### POST /versions/create
Create a new version of content.

**Request:**
```json
{
  "content_id": "string (required)",
  "changes": "object describing changes",
  "comment": "version description"
}
```

**Response:**
```json
{
  "version_id": "string",
  "content_id": "string",
  "status": "draft|pending_approval|approved",
  "created_at": "ISO8601 datetime"
}
```

### GET /versions/{content_id}
List all versions for content.

**Query Parameters:**
- `limit` (optional) - Max versions to return (default: 50)
- `status` (optional) - Filter by version status

**Response:**
```json
[
  {
    "version_id": "string",
    "status": "string",
    "creator": "user_id",
    "created_at": "ISO8601 datetime",
    "comment": "string"
  }
]
```

### POST /versions/compare
Compare two versions of content.

**Request:**
```json
{
  "base_version": "version_id",
  "target_version": "version_id",
  "format": "diff|visual|summary"
}
```

**Response:**
```json
{
  "changes": [
    {
      "field": "string",
      "old_value": "mixed",
      "new_value": "mixed"
    }
  ],
  "conflicts": ["array", "of", "conflicts"]
}
```

### POST /versions/rollback
Roll back to a previous version.

**Request:**
```json
{
  "version_id": "string (required)",
  "reason": "optional rollback reason"
}
```

**Response:**
```json
{
  "status": "rolled_back",
  "current_version": "version_id",
  "rolled_back_to": "version_id"
}
```

### Error Responses
```json
{
  "error": "version_conflict",
  "message": "Cannot rollback - newer versions exist",
  "newer_versions": ["array", "of", "version_ids"]
}