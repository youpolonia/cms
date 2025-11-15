# Content Version and Archive API

## Version Comparison
- **URL**: `GET /content/{id}/versions/compare`
- **Parameters**: 
  - `version1`: First version ID to compare (optional, defaults to latest)
  - `version2`: Second version ID to compare (optional, defaults to previous)
- **Response**:
  ```json
  {
    "content_diff": "HTML diff result",
    "metadata_diff": {
      "author": true|false,
      "created_at": true|false,
      "updated_at": true|false
    }
  }
  ```

## Version Restoration
- **URL**: `POST /content/{content}/versions/{version}/restore`
- **Parameters** (body):
  - `confirmation`: Must be `true`
  - `notes`: Optional restoration notes (max 500 chars)
- **Response**:
  ```json
  {
    "success": true,
    "message": "Version restored successfully",
    "data": {
      "content_id": 123,
      "restored_version": {
        "id": 456,
        "version_number": 5,
        "created_at": "2025-05-03T02:33:07Z"
      }
    }
  }
  ```

## Audit Log
Each restoration creates an audit log entry with:
- User who performed restoration
- Timestamp
- Version restored from
- Previous content state
- Restoration notes

## Archive System

### Archive Status Management
- **URL**: `POST /content/{id}/archive`
- **Parameters** (body):
  - `status`: Required (true/false)
  - `reason`: Optional archive reason (max 200 chars)
- **Response**:
  ```json
  {
    "success": true,
    "archived": true,
    "content_id": 123,
    "version_archived": 5
  }
  ```

### Search Filtering
Archived content can be filtered using the Search API with:
- `filters[archived]=true` - Only archived content
- `filters[archived]=false` - Only active content

### Version Archival
When content is archived:
- Current version is marked as archived
- New versions cannot be created while archived
- Restoration creates a new active version