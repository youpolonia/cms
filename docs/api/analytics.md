# Analytics API

## Endpoints

### Create Export
`POST /api/analytics/exports`

**Request:**
```json
{
  "name": "Monthly Report",
  "format": "csv",
  "filters": {
    "date_from": "2025-04-01",
    "date_to": "2025-04-30"
  }
}
```

**Response:**
```json
{
  "success": true,
  "export": {
    "id": 1,
    "name": "Monthly Report",
    "status": "pending",
    "created_at": "2025-04-25T18:00:00Z"
  }
}
```

### Get Version Stats
`GET /api/analytics/versions/{versionId}/stats`

**Response:**
```json
{
  "total_views": 1500,
  "unique_visitors": 750,
  "avg_time_spent": 2.5
}
```

### Compare Versions
`GET /api/analytics/compare/{version1Id}/{version2Id}`

**Response:**
```json
{
  "version1": {
    "total_views": 1500,
    "unique_visitors": 750
  },
  "version2": {
    "total_views": 1200,
    "unique_visitors": 600
  },
  "comparison": {
    "views_diff": 300,
    "visitors_diff": 150
  }
}
```

### Get Recent Exports
`GET /api/analytics/exports/recent`

**Response:**
```json
{
  "exports": [
    {
      "id": 1,
      "name": "Weekly Report",
      "created_at": "2025-04-24T10:00:00Z"
    }
  ],
  "count": 1
}
```

### Predictive Analytics

#### Train Model
`POST /api/analytics/train-model`

**Response:**
```json
{
  "message": "Model training completed"
}
```

#### Get Performance Prediction
`GET /api/analytics/predict/{contentId}`

**Response:**
```json
{
  "content_id": 123,
  "prediction": {
    "expected_engagement": 0.85,
    "conversion_probability": 0.72
  }
}
```

#### Compare Versions (Predictive)
`GET /api/analytics/compare/{version1Id}/{version2Id}`

**Response:**
```json
{
  "version1": {
    "id": 1,
    "predicted_engagement": 0.82
  },
  "version2": {
    "id": 2,
    "predicted_engagement": 0.91
  },
  "difference": {
    "engagement": 0.09,
    "conversion": 0.12
  }
}
```

## Authentication
All endpoints require `Authorization: Bearer {token}` header.

## Error Responses
- `401 Unauthorized` - Missing or invalid token
- `404 Not Found` - Version not found
- `422 Unprocessable Entity` - Invalid request data

## Endpoints

### `GET /api/content/analytics`

Returns aggregated content performance metrics including:
- Content views and engagement
- Category distribution
- User segments
- Version comparison statistics

#### Response
```json
{
  "category_stats": [
    {
      "name": "string",
      "contents_count": number,
      "avg_views": number,
      "avg_engagement": number
    }
  ],
  "top_content": [
    {
      "title": "string",
      "views": number,
      "engagement_score": number
    }
  ],
  "version_comparisons": {
    "total_comparisons": number,
    "avg_changes": {
      "chars": number,
      "words": number,
      "lines": number
    },
    "most_compared": [
      {
        "from_version": number,
        "to_version": number,
        "comparison_count": number
      }
    ]
  }
}
```

### `POST /api/content/export-analytics`

Generates an analytics export file (CSV or JSON)

#### Request Body
```json
{
  "type": "csv|json"
}
```

#### Response
```json
{
  "success": true,
  "message": "Export started"
}
```

## Version Comparison Analytics

### `GET /api/content/{content_id}/versions/analytics/views-over-time`

Returns time-based analytics for version comparisons of a specific content item.

#### Response
```json
{
  "labels": ["date1", "date2"],
  "data": [count1, count2]
}
```

#### Example
```json
{
  "labels": ["2025-04-01", "2025-04-02"],
  "data": [5, 12]
}
```

### `GET /api/content/{content_id}/versions/analytics/device-breakdown`

Returns device type distribution for version comparisons.

#### Response
```json
{
  "labels": ["device1", "device2"],
  "data": [count1, count2]
}
```

#### Example
```json
{
  "labels": ["desktop", "mobile"],
  "data": [42, 18]
}
```

### `GET /api/content/{content_id}/versions/analytics/location-breakdown`

Returns geographic distribution for version comparisons.

#### Response
```json
{
  "labels": ["country1", "country2"],
  "data": [count1, count2]
}
```

#### Example
```json
{
  "labels": ["US", "GB"],
  "data": [35, 25]
}
```

## Version Comparison Metrics

The analytics API tracks:
- Character/word/line changes between versions
- Frequency of version comparisons
- Most compared version pairs
- Restoration statistics

Example version comparison data:
```json
{
  "from_version": 1,
  "to_version": 2,
  "character_changes": 245,
  "word_changes": 42,
  "line_changes": 8,
  "compared_at": "datetime"
}

## Workflow Integration

### Export Status Values
- `pending` - Export queued
- `processing` - Export in progress
- `completed` - Export ready for download
- `failed` - Export failed
- `archived` - Export older than 30 days

### Performance Thresholds
- Export generation: ≤2s (small), ≤10s (large)
- Analytics queries: ≤0.5s
- Concurrent exports: 3 max per user

### Locking Mechanism
```php
DB::transaction(function () use ($exportId) {
    $export = Export::lockForUpdate()->find($exportId);
    // Process export operations
});
```

### Audit Logging
- `/api/analytics/audit` - Get analytics operation logs
- `/api/analytics/performance` - Get performance metrics
- Logs include:
  - Operation type (export/query)
  - User ID
  - Timestamp
  - Status
  - Performance metrics
  - Data volume processed
