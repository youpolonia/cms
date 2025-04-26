# Content Generation with OpenAI

## Overview
This document describes the OpenAI content generation integration in our CMS.

## Configuration
1. Add your OpenAI API key to `.env`:
```
OPENAI_API_KEY=your-api-key-here
OPENAI_ORGANIZATION=your-org-id
```

## API Endpoint
- **URL**: POST `/api/content/suggestions`
- **Authentication**: Sanctum token required
- **Request Body**:
```json
{
  "prompt": "Your content prompt",
  "context": {
    "optional": "context data"
  }
}
```
- **Response**:
```json
{
  "success": true,
  "suggestion": "Generated content",
  "usage": {
    "tokens": 100,
    "cost": 0.002
  }
}
```

## Rate Limits
- 60 requests per minute per user
- Tracked via cache with 1-minute window

## Cost Tracking
- Each user's token usage is tracked in:
  - `ai_usage_count` (total tokens)
  - `ai_usage_cost` (estimated cost)

## Frontend Integration
The content generator is available at `/content/generator` route.

## Testing
1. Set up test OpenAI key
2. Visit `/content/generator`
3. Enter prompt and generate content
4. Verify usage tracking in user table