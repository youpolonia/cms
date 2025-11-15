# AI Suggestions API Reference

## Base URL
`https://api.example.com/v1/suggestions`

## Authentication
Requires valid JWT token in Authorization header:
```
Authorization: Bearer {token}
```

## Endpoints

### GET /content
Get content suggestions for current user

#### Parameters
- `limit`: Number of suggestions to return (default: 5)
- `content_type`: Filter by content type (optional)

#### Example Request
```http
GET /v1/suggestions/content?limit=3&content_type=article
Authorization: Bearer abc123
```

#### Response
```json
{
  "suggestions": [
    {
      "id": "cont_123",
      "title": "Advanced AI Techniques",
      "type": "article",
      "score": 0.92,
      "reason": "Matches your reading history"
    }
  ]
}
```

### POST /feedback
Submit feedback on suggestions

#### Request Body
```json
{
  "suggestion_id": "cont_123",
  "rating": 5,
  "comment": "Very relevant"
}
```

#### Response
```json
{
  "status": "success",
  "message": "Feedback recorded"
}
```

## Error Codes
| Code | Description |
|------|-------------|
| 401  | Unauthorized |
| 403  | Forbidden |
| 429  | Rate limit exceeded |
| 500  | Internal server error |