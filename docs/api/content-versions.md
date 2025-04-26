# Content Versions API

## Version Restoration

### POST `/api/content/{content}/versions/{version}/restore`

Restores a specific content version.

#### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| reason | string | Yes | Reason for restoration (10-500 chars) |
| create_new_version | boolean | No | Whether to create backup version (default: true) |

#### Request Example
```json
{
  "reason": "Restoring previous better version",
  "create_new_version": true
}
```

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| success | boolean | Operation status |
| message | string | Result message |
| data.content_id | integer | ID of restored content |
| data.original_version_id | integer | ID of version being restored from |
| data.restored_version_id | integer | ID of newly created restored version |
| data.previous_version_id | integer | ID of backup version (if created) |
| data.restored_at | datetime | When restoration occurred |
| data.new_version_number | integer | Version number of restored version |

#### Response Example (Success)
```json
{
  "success": true,
  "message": "Version restored successfully",
  "data": {
    "content_id": 123,
    "original_version_id": 456,
    "restored_version_id": 789,
    "previous_version_id": 455,
    "restored_at": "2025-04-24T11:42:08Z",
    "new_version_number": 12
  }
}
```

#### Response Example (Error)
```json
{
  "success": false,
  "message": "Version restoration failed: reason",
  "errors": {
    "reason": ["The reason field is required."]
  }
}
```

## Version Comparison

### GET `/api/content/{content}/versions/{oldVersion}/compare/{newVersion}`

Compares two content versions.

#### Response Fields

| Field | Type | Description |
|-------|------|-------------|
| content_id | integer | Content ID |
| old_version | object | Old version details |
| new_version | object | New version details |
| comparison.content_diff | object | Content differences |
| comparison.meta_diff | object | Metadata differences |
| comparison.seo_diff | object | SEO differences |

#### Response Example
```json
{
  "content_id": 123,
  "old_version": {
    "id": 456,
    "version_number": 10,
    "created_at": "2025-04-20T09:00:00Z",
    "user_id": 1
  },
  "new_version": {
    "id": 789,
    "version_number": 11,
    "created_at": "2025-04-21T14:30:00Z",
    "user_id": 2
  },
  "comparison": {
    "content_diff": {
      "added": ["New paragraph added"],
      "removed": ["Old section removed"],
      "changed": ["Introduction updated"]
    },
    "meta_diff": {
      "author": true,
      "created_at": true,
      "updated_at": true,
      "status": false
    },
    "seo_diff": {
      "title": "Old vs New",
      "description": "Updated description"
    }
  }
}