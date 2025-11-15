# AI Content Generation Integration Guide

## Frontend Integration

### Using AIGeneratorService
```javascript
import { AIGeneratorService } from '@/services/page-builder/AIGeneratorService';

const generator = new AIGeneratorService();
const result = await generator.generateContent({
  prompt: "Write a blog post introduction about AI content generation",
  content_type: "html",
  tone: "professional"
});
```

### Available Methods
- `generateContent(params)`: Main generation method
- `getUsage()`: Returns current usage stats
- `getTemplates()`: Lists available templates
- `savePrompt(name, template)`: Saves a prompt template

## Backend API Integration

### Authentication
```http
POST /api/content/generate
Authorization: Bearer {api_token}
Content-Type: application/json

{
  "prompt": "Product description for smartwatch",
  "content_type": "text",
  "model": "gpt-3.5-turbo"
}
```

### Response Handling
```javascript
{
  "success": true,
  "content": "...",
  "usage": {
    "tokens": 125,
    "cost": 0.0025
  }
}
```

## Webhooks

### Configuration
Set up webhooks in the admin panel to receive:
- Generation completed notifications
- Usage limit alerts
- Moderation events

### Sample Payload
```json
{
  "event": "generation.completed",
  "data": {
    "request_id": "gen_12345",
    "user_id": 42,
    "tokens_used": 89,
    "timestamp": "2025-04-27T15:00:00Z"
  }
}
```

## Rate Limiting
- 10 requests per minute
- 500 requests per day
- 429 status code when exceeded

## Error Handling
Common error codes:
- 400: Invalid request
- 401: Unauthorized
- 403: Forbidden (quota exceeded)
- 429: Rate limited
- 500: Server error