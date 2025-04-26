# Content Generation API Documentation

## Base URL
`https://api.example.com/v1`

## Authentication
All endpoints require an API key:
```
Authorization: Bearer {API_KEY}
```

## Endpoints

### Generate Content
`POST /content/generate`

**Request:**
```json
{
  "prompt": "string (required)",
  "content_type": "string (required)",
  "tone": "professional|casual|friendly|authoritative|creative|technical (required)",
  "length": "short|medium|long|extended (required)",
  "style": "string (required)",
  "target_audience": "string (required)",
  "model": "string (optional)",
  "language": "string (optional)",
  "validation_rules": "array (optional)",
  "examples": "array (optional)"
}
```

**Response:**
```json
{
  "content": "string",
  "tokens_used": "integer",
  "model": "string",
  "created_at": "timestamp"
}
```

### Suggest Categories
`POST /content/suggest-categories`

**Request:**
```json
{
  "prompt": "string (required)",
  "language": "string (optional)"
}
```

**Response:**
```json
{
  "categories": ["string"],
  "confidence_scores": ["float"]
}
```

### Get Usage Stats
`GET /content/usage-stats`

**Response:**
```json
{
  "daily": "integer",
  "daily_limit": "integer",
  "daily_remaining": "integer",
  "monthly": "integer",
  "monthly_limit": "integer",
  "monthly_remaining": "integer",
  "can_use": "boolean"
}
```

### Check Usage
`GET /content/check-usage`

**Response:**
```json
{
  "can_use": "boolean",
  "daily_usage": "integer",
  "daily_limit": "integer",
  "monthly_usage": "integer",
  "monthly_limit": "integer",
  "remaining": "integer",
  "limit_type": "string|null"
}
```

## Rate Limits
- 100 requests/minute
- 10,000 tokens/day per user (configurable)
- 100,000 tokens/month per user (configurable)

## Error Codes
- 400: Bad Request
- 401: Unauthorized
- 422: Validation Error
- 429: Rate Limit Exceeded
- 500: Server Error