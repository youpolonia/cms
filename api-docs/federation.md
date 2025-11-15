# Content Federation API Documentation

## Overview
The Content Federation API enables sharing and synchronization of content between different tenants in the CMS. It includes endpoints for sharing content, listing available federated content, synchronizing versions, and resolving conflicts.

## Authentication
All endpoints require authentication with a valid API token having either `admin` or `content_manager` role.

## Rate Limits
- Maximum 100 requests per minute per IP
- Responses include `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

## Endpoints

### POST /federation/share
Share content with one or more target tenants.

**Request:**
```json
{
  "content_id": "string (required)",
  "target_tenants": ["array", "of", "tenant", "ids"],
  "options": {
    "permission_map": {
      "view": true,
      "edit": false,
      "share": false
    },
    "expires_at": "optional ISO8601 datetime"
  }
}
```

**Response:**
```json
{
  "tenant_id1": {"status": "success"},
  "tenant_id2": {"status": "error", "message": "Error details"}
}
```

### GET /federation/list
List available federated content with optional filters.

**Query Parameters:**
- `content_id` (optional) - Filter by specific content ID

**Response:**
```json
[
  {
    "content_id": "string",
    "source_tenant": "string",
    "target_tenant": "string",
    "version_hash": "string",
    "shared_at": "ISO8601 datetime"
  }
]
```

### POST /federation/sync
Synchronize content versions across tenants.

**Request:**
```json
{
  "content_id": "string (required)",
  "tenant_ids": ["array", "of", "tenant", "ids"]
}
```

**Response:**
```json
{
  "tenant_id1": {"status": "up_to_date"},
  "tenant_id2": {"status": "synced"},
  "tenant_id3": {"status": "conflict", "conflict_id": "string"}
}
```

### POST /federation/resolve
Resolve content conflicts between versions.

**Request:**
```json
{
  "conflict_id": "string (required)",
  "strategy": "semantic|sectional|hybrid|manual",
  "resolution_data": {
    // strategy-specific data
  }
}
```

**Response:**
```json
{
  "status": "resolved",
  "resolution_id": "string",
  "applied_at": "ISO8601 datetime"
}
```

## Error Responses
All endpoints return standard error responses:

```json
{
  "error": "Error message"
}
```

With appropriate HTTP status codes:
- 400 Bad Request - Invalid input
- 401 Unauthorized - Missing/invalid auth
- 403 Forbidden - Insufficient permissions
- 429 Too Many Requests - Rate limit exceeded
- 500 Internal Server Error - Server error