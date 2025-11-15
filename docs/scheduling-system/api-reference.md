# Scheduling System API Reference

## Base URL
`/api/scheduling`

## Authentication
Requires valid session cookie or API key header:
```
X-API-Key: your_api_key_here
```

## Endpoints

### Create Schedule
`POST /schedules`
```json
{
  "content_id": "123",
  "action": "publish",
  "schedule_time": "2025-05-12T09:00:00Z",
  "recurring": false
}
```

### Get Scheduled Items
`GET /schedules`
```json
{
  "schedules": [
    {
      "id": "456",
      "content_id": "123",
      "action": "publish",
      "schedule_time": "2025-05-12T09:00:00Z",
      "status": "pending"
    }
  ]
}
```

### Error Responses
```json
{
  "error": {
    "code": 400,
    "message": "Invalid schedule time"
  }
}
```

## Rate Limits
- 100 requests/minute
- 1000 requests/hour