# Theme Version Rollbacks API

## Overview
The Theme Version Rollbacks API provides analytics and management capabilities for theme version rollback operations.

## Rollback Analytics Service

### getRollbackStats()
Returns detailed statistics about a specific rollback operation.

**Parameters:**
- `ThemeVersionRollback $rollback` - The rollback instance to analyze

**Response:**
```json
{
  "id": 1,
  "status": "completed",
  "completed_at": "2025-04-14T00:00:00.000000Z",
  "duration": 120,
  "version": {
    "id": 123,
    "name": "v1.2.3"
  },
  "rollback_to_version": {
    "id": 122,
    "name": "v1.2.2"
  },
  "error_message": null
}
```

### getRecentRollbacks()
Returns a list of recent rollback operations.

**Parameters:**
- `int $limit` - Number of recent rollbacks to return (default: 10)

**Response:**
```json
[
  {
    "id": 1,
    "status": "completed",
    "created_at": "2025-04-14T00:00:00.000000Z",
    "version_name": "v1.2.3",
    "rollback_to_version_name": "v1.2.2"
  }
]
```

### getSuccessRate()
Calculates the success rate of rollback operations for a specific theme.

**Parameters:**
- `int $themeId` - The ID of the theme to analyze

**Response:**
```json
60.0
```

## Endpoints

### GET /api/themes/{theme}/rollbacks/analytics
Returns rollback analytics for a theme.

**Parameters:**
- `theme` - Theme ID
- `limit` - (optional) Number of recent rollbacks to include (default: 10)

**Response:**
```json
{
  "success_rate": 75.0,
  "recent_rollbacks": [
    {
      "id": 1,
      "status": "completed",
      "created_at": "2025-04-14T00:00:00.000000Z",
      "version_name": "v1.2.3",
      "rollback_to_version_name": "v1.2.2"
    }
  ]
}
```

### GET /api/themes/{theme}/rollbacks/{rollback}/stats
Returns detailed stats for a specific rollback operation.

**Response:**
Same as `getRollbackStats()` response above
