# Memory Management API

## Base URL
`/api/memory`

## Endpoints

### Get Current Memory Usage
`GET /usage`

**Response:**
```json
{
  "current": 128.5,
  "max_allowed": 512,
  "percentage": 25.1
}
```

### Configure Memory Limits
`POST /configure`
```json
{
  "max_usage": 512,
  "chunk_size": 1024,
  "cleanup_interval": 60
}
```

**Response:**
```json
{
  "status": "success",
  "new_settings": {
    "max_usage": 512,
    "chunk_size": 1024,
    "cleanup_interval": 60
  }
}
```

### Get Memory Alerts
`GET /alerts`

**Response:**
```json
[
  {
    "timestamp": "2025-05-24T23:40:12Z",
    "message": "Memory usage exceeded 80% threshold",
    "severity": "warning"
  }
]
```

## Error Responses
```json
{
  "error": "invalid_configuration",
  "message": "Max usage cannot exceed 1024MB"
}