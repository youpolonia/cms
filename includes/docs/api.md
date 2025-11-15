# API Documentation - Version Control System

## Authentication
- All endpoints require JWT authentication except:
  - `/api/auth/login`
  - `/api/auth/register`
  - `/api/health`

## Version Control Endpoints

### Get Content Versions
`GET /api/content/{id}/versions`

**Parameters:**
- `id` (required): Content item ID

**Response:**
```json
{
  "versions": [
    {
      "id": 123,
      "version_number": 1,
      "created_at": "2025-05-31T12:00:00Z",
      "created_by": "user@example.com",
      "notes": "Initial version"
    }
  ]
}
```

### Restore Version
`POST /api/content/versions/{id}/restore`

**Parameters:**
- `id` (required): Version ID to restore

**Response:**
```json
{
  "success": true,
  "message": "Version restored",
  "current_version": 2
}
```

### Delete Version
`DELETE /api/content/versions/{id}`

**Parameters:**
- `id` (required): Version ID to delete

**Response:**
```json
{
  "success": true,
  "message": "Version deleted"
}
```

## Error Responses
```json
{
  "error": {
    "code": 401,
    "message": "Unauthorized"
  }
}
```

| Code | Description |
|------|-------------|
| 400 | Bad Request |
| 401 | Unauthorized |
| 404 | Not Found |
| 500 | Server Error |