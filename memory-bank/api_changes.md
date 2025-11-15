# Version History API Documentation

## Endpoints

### GET /api/versions
Lists all versions with pagination and filtering

**Parameters:**
- `content_type` - Filter by content type
- `date_from` - Filter versions created after this date (YYYY-MM-DD)
- `date_to` - Filter versions created before this date (YYYY-MM-DD)
- `search` - Search in content or content_type
- `sort` - Field to sort by (id, content_type, user_id, created_at)
- `order` - Sort order (ASC/DESC)
- `limit` - Items per page (default: 20)
- `offset` - Pagination offset (default: 0)

**Response:**
```json
{
  "data": [/* array of version objects */],
  "total": 100,
  "limit": 20,
  "offset": 0
}
```

### GET /api/versions/{id}
Get specific version by ID

**Response:**
```json
{
  "id": 123,
  "content_id": 456,
  "content": "...",
  "content_type": "page",
  "user_id": 789,
  "created_at": "2025-05-01 12:34:56"
}
```

### GET /api/content/{id}/versions
Get all versions for specific content item

**Response:**
```json
[
  {/* version object */},
  {/* version object */}
]
```

## Implementation Notes
- Uses existing VersionModel for database operations
- Supports pagination and filtering
- Returns standardized API responses
- Follows REST conventions