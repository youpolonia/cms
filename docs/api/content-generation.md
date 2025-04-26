# Content Generation API

## Endpoint
`POST /api/content/generate`

## Authentication
Requires Sanctum token in Authorization header:
`Authorization: Bearer {token}`

## Request Body
```json
{
  "prompt": "string",
  "type": "generate|rewrite|summarize",
  "content_type": "post|page|product",
  "tone": "professional|casual|friendly",
  "length": "short|medium|long", 
  "style": "concise|detailed|creative"
}
```

## Response
```json
{
  "success": true,
  "content": "string",
  "usage": {
    "prompt_tokens": number,
    "completion_tokens": number,
    "total_tokens": number
  },
  "message": "string"
}

## Page Builder AI Integration

The Page Builder now includes AI-powered content generation and block suggestions:

### Content Generation
`POST /api/page-builder/generate-content`

**Request:**
```json
{
  "prompt": "string"
}
```

**Response:**
```json
{
  "content": "string",
  "suggestions": ["string"]
}
```

### Block Suggestions
`POST /api/page-builder/suggest-blocks`

**Request:**
```json
{
  "currentBlocks": ["array"]
}
```

**Response:**
```json
{
  "blocks": ["array"],
  "layout": "string"
}
```

### Usage Example
```javascript
// Generate content
const response = await fetch('/api/page-builder/generate-content', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer {token}'
  },
  body: JSON.stringify({ prompt: 'Blog post about AI' })
});

// Get block suggestions
const suggestions = await fetch('/api/page-builder/suggest-blocks', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': 'Bearer {token}'
  },
  body: JSON.stringify({ currentBlocks: existingBlocks })
});
```
```

## Example Request
```bash
curl -X POST http://localhost:8000/api/content/generate \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "prompt": "Test content",
    "type": "generate",
    "content_type": "post",
    "tone": "professional",
    "length": "medium",
    "style": "detailed"
  }'
```

## Content Scheduling

The system supports scheduled publishing and expiration of content with these features:

- **Scheduled Publishing**: Set a future publish date
- **Recurring Content**: Automatically create new instances on a schedule
- **Expiration**: Automatically unpublish content after a set date
- **Notifications**: Users receive alerts when content is published/expired

### Scheduling Request Example
```json
{
  "title": "Scheduled Post",
  "content": "This will publish later",
  "publish_at": "2025-04-08 09:00:00",
  "is_recurring": true,
  "recurring_frequency": "weekly",
  "recurring_end": "2025-12-31"
}
```

### Scheduling Response
```json
{
  "success": true,
  "message": "Content scheduled successfully",
  "next_publish": "2025-04-08 09:00:00"
}
