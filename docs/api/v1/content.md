# Content API Documentation - Version 1

## Base Endpoints
- `POST /api/v1/content` - Create new content
- `GET /api/v1/content/{id}` - Get content by ID  
- `PUT /api/v1/content/{id}` - Update content
- `DELETE /api/v1/content/{id}` - Delete content

## Version Control Endpoints

### Create Version
`POST /api/v1/content/{id}/versions`

Creates a new version snapshot of the content.

**Parameters:**
- `comment` (string): Description of changes in this version

**Response:**
```json
{
  "version_id": "uuid-string",
  "created_at": "2025-05-31T01:33:17Z"
}
```

### List Versions
`GET /api/v1/content/{id}/versions`

Returns all versions for specified content.

**Response:**
```json
[
  {
    "version_id": "uuid-string",
    "created_at": "2025-05-31T01:33:17Z",
    "comment": "Initial version"
  }
]
```

### Get Version
`GET /api/v1/content/{id}/versions/{version_id}`

Returns the content as it existed at the specified version.

**Response:**
Full content object in historical state

### Compare Versions
`POST /api/v1/content/{id}/versions/compare`

Compares two versions of content.

**Parameters:**
- `version1` (string): First version ID
- `version2` (string): Second version ID

**Response:**
```json
{
  "differences": [
    {
      "field": "title",
      "old_value": "Old Title",
      "new_value": "New Title"
    }
  ]
}
```

### Restore Version
`POST /api/v1/content/{id}/versions/restore`

Rolls content back to specified version.

**Parameters:**
- `version_id` (string): Version ID to restore

**Response:**
Updated content object