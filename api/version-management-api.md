# Version Management API Specification

## Base URL
`/api/content`

## Endpoints

### 1. Get Content Versions
**Endpoint**: `GET /{contentId}/versions`  
**Description**: Retrieve all versions for a content item  
**Parameters**:
- `contentId` (path): ID of the content item

**Response**:
```json
[
  {
    "id": "string",
    "version_number": "number",
    "created_at": "ISO8601 datetime",
    "author_name": "string",
    "change_type": "string"
  }
]
```

### 2. Restore Version
**Endpoint**: `POST /versions/{versionId}/restore`  
**Description**: Restore a specific version as the current version  
**Parameters**:
- `versionId` (path): ID of the version to restore

**Response**:
- 200 OK on success
- 404 Not Found if version doesn't exist
- 500 Server Error on failure

### 3. Delete Version
**Endpoint**: `DELETE /versions/{versionId}`  
**Description**: Permanently delete a version  
**Parameters**:
- `versionId` (path): ID of the version to delete

**Response**:
- 200 OK on success
- 404 Not Found if version doesn't exist
- 500 Server Error on failure

## Error Responses
```json
{
  "error": "string",
  "message": "string",
  "timestamp": "ISO8601 datetime"
}
```

## Rate Limiting
- 100 requests/minute per IP
- 1000 requests/day per API key

## Authentication
Required for all write operations (POST, DELETE):
- `Authorization: Bearer {token}`