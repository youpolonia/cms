# Collaboration API Documentation

## Authentication
All endpoints require JWT authentication. Include the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

## Rate Limits
- Global: 120 requests/minute
- Per-user: 30 requests/minute  
- Per-session: 60 requests/minute

## Endpoints

### Create Session
`POST /api/collaboration/sessions`

**Request:**
```json
{
  "content_id": 1,
  "content": "Initial content"
}
```

**Response:**
```json
{
  "id": 1,
  "content_id": 1,
  "owner_id": 1,
  "content": "Initial content"
}
```

### Join Session  
`POST /api/collaboration/sessions/{session}/join`

**Response:**
```json
{
  "message": "Joined session successfully"
}
```

### Leave Session
`POST /api/collaboration/sessions/{session}/leave`

**Response:**
```json
{
  "message": "Left session successfully"
}
```

### Update Content
`POST /api/collaboration/sessions/{session}/content`

**Request:**
```json
{
  "operations": [
    {
      "type": "insert",
      "position": 5,
      "text": "new text"
    }
  ]
}
```

### Add Comment
`POST /api/collaboration/sessions/{session}/comments`

**Request:**
```json
{
  "text": "This is a comment"
}
```

**Response:**
```json
{
  "id": 1,
  "session_id": 1,
  "user_id": 1,
  "text": "This is a comment",
  "created_at": "2025-04-30T01:20:00Z"
}
```

## Error Responses
```json
{
  "message": "Error message",
  "code": 400
}