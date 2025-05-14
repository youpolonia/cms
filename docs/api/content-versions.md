# Content Version API

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