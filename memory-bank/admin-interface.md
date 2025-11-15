# Admin Interface Documentation

## Version Browser

### Feature Description
The Version Browser provides a complete history of all content versions with:
- Paginated listing of versions
- Detailed version metadata
- Search capabilities by date range and content
- Sorting by creation date (newest first)

### Usage Instructions
1. Access the Version Browser through the admin interface
2. Use the pagination controls to navigate through versions
3. Search for specific versions using:
   - Date range filters
   - Content search terms
4. Click any version to view full metadata

### Technical Specifications
- Built on PHP/MySQL backend
- Uses VersionModel for database operations
- RESTful API endpoints return JSON responses
- Supports pagination (default 20 items per page)
- Maximum 50 items per page allowed

### API Integration
Available endpoints:

1. List versions (paginated)
```http
GET /versions?page=1&per_page=20
```

2. Get version metadata
```http
GET /versions/{id}
```

3. Search versions
```http
GET /versions/search?date_from=2025-01-01&date_to=2025-12-31&q=search_term
```

Response format (list endpoint):
```json
{
  "data": [/* version objects */],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

## Diff Viewer

### Feature Description
The Diff Viewer provides comprehensive comparison of content versions with:
- Side-by-side and inline diff views
- Line-level and word-level change highlighting
- Special handling for HTML content
- Color-coded changes (additions, deletions, modifications)
- Navigation between changes

### Usage Instructions
1. Select two versions to compare from the Version Browser
2. Choose view mode (side-by-side or inline)
3. Use navigation controls to jump between changes
4. Hover over changes for detailed word-level differences
5. Export diff report if needed

### Technical Specifications
- Implements Myers diff algorithm (O(ND) complexity)
- Supports both text and HTML content
- Normalizes HTML before comparison
- Provides formatted output for human readability
- Memory-efficient operations for large documents
- API returns structured diff data

### API Integration
To compare two versions programmatically:

```php
// Basic usage
$diff = DiffEngine::compare($oldContent, $newContent, $isHtml);

// Get formatted output
$formatted = DiffEngine::formatDiff($diff);
```

Response format:
```json
[
  {
    "type": "change",
    "line": 42,
    "old_content": "Original text",
    "new_content": "Modified text",
    "word_diff": [
      {
        "type": "delete",
        "old_pos": 3,
        "content": "removed"
      },
      {
        "type": "insert",
        "new_pos": 3,
        "content": "added"
      }
    ]
  }
]
```

## Restoration Panel

### Feature Description
The Restoration Panel provides safe content version restoration with:
- Preview of changes before restoration
- Validation of restorable versions
- Complete restoration history/audit log
- User attribution for all restorations
- Protection against invalid restorations

### Usage Instructions
1. Select a version to restore from the Version Browser
2. Preview changes using the "Preview" button
3. Validate restoration eligibility if needed
4. Confirm restoration when ready
5. View restoration history for audit purposes

### Technical Specifications
- Uses transactional database operations
- Maintains complete restoration audit trail
- Requires authentication for all operations
- Validates version integrity before restoration
- Stores restoration metadata (user, timestamp)
- API returns structured responses

### API Integration
Available endpoints:

1. Restore a version (POST)
```http
POST /restore/{id}
Authorization: Bearer {token}
```

2. Preview restoration (GET)
```http
GET /restore/preview/{id}
```

3. Validate restoration (GET)
```http
GET /restore/validate/{id}
```

4. Get restoration history (GET)
```http
GET /restore/history?page=1&per_page=20
```

Response format (history endpoint):
```json
{
  "data": [
    {
      "id": 123,
      "version_id": 456,
      "user_id": 789,
      "restored_at": "2025-05-11T01:00:00Z",
      "content_id": 101,
      "version_number": 42
    }
  ],
  "pagination": {
    "total": 100,
    "per_page": 20,
    "current_page": 1,
    "last_page": 5
  }
}
```

## Bulk Operations

### Feature Description
The Bulk Operations panel enables efficient management of multiple content versions simultaneously:
- Delete multiple versions at once
- Restore multiple versions (creates new versions)
- Update metadata for multiple versions
- Visual confirmation for destructive actions
- Protection against modifying current version

### Usage Instructions
1. Navigate to Bulk Operations from the Version Browser
2. Select versions using checkboxes (current version is disabled)
3. Choose an action:
   - Delete: Permanently removes selected versions
   - Restore: Creates new versions from selected ones
   - Update Metadata: Modify version attributes
4. Confirm destructive actions in the dialog
5. View results in the version list

### Technical Specifications
- Uses ContentVersionModel for database operations
- Implements CSRF protection for all operations
- Prevents modification of current version
- Maintains version integrity during bulk operations
- Provides visual feedback for all actions
- Uses optimized batch database operations

### API Integration
Available endpoints:

1. Bulk Delete (POST)
```http
POST /api/version/bulk-delete
Headers:
  X-CSRF-Token: {token}
  Content-Type: application/json

Body:
{
  "content_id": 123,
  "version_ids": [456, 789]
}
```

2. Bulk Restore (POST)
```http
POST /api/content/bulk-restore
Headers:
  X-CSRF-Token: {token}
  Content-Type: application/json

Body:
{
  "content_id": 123,
  "version_ids": [456, 789]
}
```

Response format (both endpoints):
```json
{
  "success": true,
  "count": 2,
  "new_version_ids": [101, 102] // Only for restore
}
```

Security Notes:
- Requires authenticated session
- Validates all version IDs belong to content
- Uses CSRF tokens for all modifications
- Logs all bulk operations