# API Documentation

## Content Endpoints

### Change Content State
`PUT /api/v1/content/{id}/state`

Changes the state of a content item. Valid states: draft, published, archived.

**Request Body:**
```json
{
    "state": "published"
}
```

**Response:**
```json
{
    "status": "success",
    "data": {
        "affected_rows": 1,
        "message": "State changed successfully"
    }
}
```

**Errors:**
- 400: Invalid state parameter
- 403: Insufficient permissions
- 404: Content not found

**Permissions:**
- Requires 'editor' role or higher
- Content must belong to current tenant