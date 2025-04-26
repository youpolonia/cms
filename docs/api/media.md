# Media API Documentation

## Authentication
All endpoints require authentication via Bearer token. Include the token in the Authorization header:
```
Authorization: Bearer {your_api_token}
```

## Endpoints

### List Media Items
`GET /api/media`

**Parameters:**
- `search` (optional): Filter by filename
- `type` (optional): Filter by type (image|video|document)
- `collection` (optional): Filter by collection ID
- `page` (optional): Pagination page number

**Example Request:**
```http
GET /api/media?type=image&page=2
Authorization: Bearer {your_api_token}
```

**Success Response (200):**
```json
{
    "data": [
        {
            "id": "uuid",
            "filename": "example.jpg",
            "path": "media/user_id/filename.jpg",
            "metadata": {
                "size": 1024,
                "mime_type": "image/jpeg"
            },
            "collections": [
                {
                    "id": 1,
                    "name": "Example Collection",
                    "is_featured": true
                }
            ]
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": "...",
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "per_page": 15,
        "total": 30
    }
}
```

### Upload Media
`POST /api/media`

**Headers:**
```
Content-Type: multipart/form-data
```

**Body Parameters:**
- `file` (required): The media file to upload
- `collections` (optional): Array of collection IDs
- `featured` (optional): Array of collection IDs where this media should be featured

**Success Response (201):**
```json
{
    "id": "uuid",
    "filename": "example.jpg",
    "path": "media/user_id/filename.jpg",
    "metadata": {
        "size": 1024,
        "mime_type": "image/jpeg"
    }
}
```

### Get Media Details
`GET /api/media/{id}`

**Success Response (200):**
```json
{
    "id": "uuid",
    "filename": "example.jpg",
    "description": "Example image",
    "path": "media/user_id/filename.jpg",
    "metadata": {
        "size": 1024,
        "mime_type": "image/jpeg"
    },
    "collections": [
        {
            "id": 1,
            "name": "Example Collection",
            "is_featured": true
        }
    ]
}
```

### Update Media Metadata
`PATCH /api/media/{id}/metadata`

**Body Parameters:**
- `filename` (optional): New filename
- `description` (optional): Description text

**Success Response (200):**
```json
{
    "id": "uuid",
    "filename": "updated.jpg",
    "description": "Updated description",
    "path": "media/user_id/filename.jpg"
}
```

### Update Media Collections
`PATCH /api/media/{id}/collections`

**Body Parameters:**
- `collections` (required): Array of collection IDs
- `featured` (optional): Array of collection IDs where this media should be featured

**Success Response (200):**
```json
{
    "id": "uuid",
    "collections": [
        {
            "id": 1,
            "name": "Example Collection",
            "is_featured": true
        },
        {
            "id": 2,
            "name": "Another Collection",
            "is_featured": false
        }
    ]
}
```

### Delete Media
`DELETE /api/media/{id}`

**Success Response (204): No Content**

## Version Control Endpoints

### List Media Versions
`GET /api/media/{id}/versions`

**Parameters:**
- `tag` (optional): Filter by tag
- `branch` (optional): Filter by branch name

**Success Response (200):**
```json
{
    "data": [
        {
            "id": "uuid",
            "version_number": 1,
            "filename": "example.jpg",
            "comment": "Initial version",
            "tags": ["initial"],
            "branch_name": "main",
            "created_at": "2025-04-10T00:00:00Z"
        }
    ]
}
```

### Compare Versions
`GET /api/media/{id}/versions/compare`

**Parameters:**
- `version1` (required): First version number
- `version2` (optional): Second version number (defaults to current)

**Success Response (200):**
```json
{
    "version1": {
        "id": "uuid",
        "version_number": 1,
        "metadata": {
            "size": 1024,
            "mime_type": "image/jpeg"
        }
    },
    "version2": {
        "id": "uuid",
        "version_number": 2,
        "metadata": {
            "size": 2048,
            "mime_type": "image/jpeg"
        }
    },
    "differences": [
        {
            "field": "size",
            "old_value": 1024,
            "new_value": 2048
        }
    ]
}
```

### Get Version Diff
`GET /api/media/{id}/versions/diff`

**Parameters:**
- `version1` (required): First version number
- `version2` (optional): Second version number (defaults to current)

**Success Response (200):**
```json
{
    "version1": {
        "id": "uuid",
        "version_number": 1
    },
    "version2": {
        "id": "uuid",
        "version_number": 2
    },
    "diffs": [
        {
            "type": "changed",
            "line": 1,
            "old": "original content",
            "new": "updated content"
        }
    ]
}
```

### Restore Version
`POST /api/media/{id}/versions/restore`

**Body Parameters:**
- `version_number` (required): Version number to restore

**Success Response (200):**
```json
{
    "message": "Restored to version 1 successfully",
    "current_version": 1
}
```

## Error Responses

**401 Unauthorized**
```json
{
    "message": "Unauthenticated."
}
```

**403 Forbidden**
```json
{
    "message": "You don't have permission to access this media."
}
```

**404 Not Found**
```json
{
    "message": "Media not found."
}
```

**422 Unprocessable Entity**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "file": ["The file must be an image."]
    }
}
