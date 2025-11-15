# Version Control System Documentation

## Overview
The CMS implements a comprehensive version control system with:
- Content versioning
- Version comparison
- Conflict resolution
- Rollback capabilities

## API Endpoints

### List Versions
`GET /api/content/{id}/versions`

**Response:**
```json
{
  "data": [
    {
      "id": "version_id",
      "version_number": 1,
      "created_at": "timestamp",
      "author_name": "string",
      "change_type": "created|updated"
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

### Compare Versions
`GET /api/content/versions/compare?from={version1}&to={version2}`

**Response:**
```json
{
  "from_version": "version_id",
  "to_version": "version_id",
  "diff": [
    {
      "type": "insert|delete|change",
      "content": "string",
      "line_number": 1
    }
  ]
}
```

### Restore Version
`POST /api/content/versions/{id}/restore`

**Response:**
```json
{
  "success": true,
  "restored_version": "version_id"
}
```

## Version History UI

### Version Listing
- Displays in a paginated table
- Shows version number, timestamp, author and change type
- Actions available per version:
  - Compare (select 2 versions)
  - Restore
  - Delete

### Comparison View
- Side-by-side diff visualization
- Color-coded changes:
  - Green for additions
  - Red for deletions
- Scrollable content area

## Conflict Resolution

### Detection
- Line-level conflict detection
- Same line modified differently in concurrent versions
- Automatic merging when possible

### Resolution Process
1. Compare conflicting versions
2. Manually select changes to keep
3. Save merged version

## Implementation Details

### Core Components
- `VersionHistory`: Manages version metadata
- `VersionDiffService`: Handles comparison logic
- `RollbackManager`: Manages version restoration

### Diff Algorithm
- Uses longest common subsequence
- Line-based comparison
- Semantic analysis for better change detection

## Example Usage

### PHP Example
```php
// Get paginated versions
$versions = VersionHistory::getPaginatedVersions(
    $page = 1,
    $perPage = 20,
    $startDate = null,
    $endDate = null
);

// Compare two versions
$diff = VersionDiffService::compare($version1, $version2);
```

### JavaScript Example
```javascript
// Compare selected versions
async function compareVersions(versionIds) {
  const response = await fetch(`/api/content/versions/compare?from=${versionIds[0]}&to=${versionIds[1]}`);
  const diff = await response.json();
  openDiffModal(diff);
}
```

## Best Practices
- Regularly review version history
- Use descriptive change comments
- Resolve conflicts promptly
- Test rollbacks in staging environment