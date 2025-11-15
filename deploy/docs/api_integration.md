# API Integration Documentation

## Federation API Endpoints

### POST /api/federation/share
- Shares content between tenants
- Requires authentication header
- Request body:
  ```json
  {
    "content": "base64_encoded_content"
  }
  ```
- Returns:
  ```json
  {
    "version_id": "uuid",
    "timestamp": "ISO8601"
  }
  ```

### GET /api/federation/sync
- Synchronizes content versions
- Query parameters:
  - `version`: Last known version ID (optional)
  - `limit`: Maximum versions to return (default: 10, max: 50)
- Returns:
  ```json
  {
    "versions": [
      {
        "id": "uuid",
        "content": "base64_encoded_content",
        "timestamp": "ISO8601"
      }
    ],
    "latest_version": "uuid",
    "timestamp": "ISO8601"
  }
  ```

### POST /api/federation/resolve
- Resolves content conflicts
- Request body:
  ```json
  {
    "conflicts": [
      {
        "version_id": "uuid",
        "content": "base64_encoded_content"
      }
    ]
  }
  ```
- Returns:
  ```json
  {
    "resolution_id": "uuid",
    "resolved_versions": ["uuid"],
    "timestamp": "ISO8601"
  }
  ```

## Rate Limiting
- API endpoints are rate limited
- Configuration in config/rate_limiting.php
- Default limits:
  - General API: 100 requests/minute
  - Federation: 30 requests/minute