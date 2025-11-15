# API Changes Documentation

## Version Diff Endpoints

### GET /api/versions/{id}/diff
- **Description**: Compare a specific version against the current content
- **Authentication**: Required (ApiAuthMiddleware)
- **Parameters**:
  - `id` (integer): The version ID to compare
- **Response Format**:
  ```json
  {
    "status": "success|error",
    "data": {
      "diff": "HTML diff output",
      "version": {
        "id": 123,
        "content_id": 456,
        "title": "Version title",
        "content": "Version content",
        "created_at": "2025-05-10 23:00:00"
      },
      "current": {
        "id": 456,
        "title": "Current title",
        "content": "Current content",
        "updated_at": "2025-05-10 23:30:00"
      }
    }
  }
  ```

### GET /api/versions/{id1}/diff/{id2}
- **Description**: Compare two specific versions against each other
- **Authentication**: Required (ApiAuthMiddleware)
- **Parameters**:
  - `id1` (integer): First version ID to compare
  - `id2` (integer): Second version ID to compare
- **Response Format**:
  ```json
  {
    "status": "success|error",
    "data": {
      "diff": "HTML diff output",
      "version1": {
        "id": 123,
        "content_id": 456,
        "title": "Version 1 title",
        "content": "Version 1 content",
        "created_at": "2025-05-10 23:00:00"
      },
      "version2": {
        "id": 124,
        "content_id": 456,
        "title": "Version 2 title",
        "content": "Version 2 content",
        "created_at": "2025-05-10 23:15:00"
      }
    }
  }
  ```

## Restoration Workflow

1. **Frontend Component** (`VersionRestore.vue`):
   - Displays version metadata and diff preview
   - Handles user confirmation
   - Makes API call to restore endpoint

2. **API Endpoint**:
   ```json
   POST /api/content-versions/{versionId}/restore
   {
     "confirm": true
   }
   ```

3. **Backend Process**:
   - Validates authentication
   - Checks version exists
   - Restores content
   - Returns success/error response

## Frontend-Backend Integration

### Required API Endpoints:
1. `GET /api/content-versions/{versionId}` - Returns version metadata
2. `GET /api/content-versions/{versionId}/diff` - Returns diff content
3. `POST /api/content-versions/{versionId}/restore` - Performs restoration

### Example Frontend Usage:
```vue
<VersionRestore 
  :versionId="selectedVersionId"
  @restored="handleRestored"
/>
```

## Authentication Requirements
- All version endpoints require `ApiAuthMiddleware`
- Uses Core/Auth system
- Requires valid session token in headers:
  ```
  Authorization: Bearer {token}
  ```

## Implementation Notes
- Uses `DiffRenderer.php` for generating HTML diffs
- All responses follow `Core/ApiResponse` format
- Follows framework-free PHP patterns
- Backward compatible with existing functionality

## Template Updates
- Added version restoration to admin dashboard
- New section includes:
  - Version selection dropdown
  - Restore button
  - Styled form controls
- No breaking changes introduced