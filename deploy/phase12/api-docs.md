# Version Control API Documentation

## Endpoints

### GET `/api/versions/list/{contentId}`
Lists all versions for specified content

**Parameters:**
- `contentId` - ID of content to list versions for

**Response:**
```json
{
  "versions": [
    {
      "id": "version1",
      "created": "2025-06-01T12:00:00Z",
      "author": "user123",
      "comment": "Initial version"
    }
  ]
}
```

### POST `/api/versions/create/{contentId}`
Creates new version of content

**Parameters:**
- `contentId` - ID of content to version

**Body:**
```json
{
  "comment": "Version description"
}
```

### GET `/api/versions/compare/{version1}/{version2}`
Compares two versions

**Parameters:**
- `version1` - First version ID
- `version2` - Second version ID

**Response:**
```json
{
  "oldText": "Original content",
  "newText": "Modified content" 
}
```

### POST `/api/versions/restore/{versionId}`
Restores specified version

**Parameters:**
- `versionId` - Version ID to restore

### DELETE `/api/versions/delete/{versionId}`
Deletes specified version

**Parameters:**
- `versionId` - Version ID to delete