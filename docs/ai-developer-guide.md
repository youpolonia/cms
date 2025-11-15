# AI Developer Guide

## Overview
The CMS provides AI integration through REST APIs for content generation and processing. The system supports:
- OpenAI (GPT models)
- Content generation and processing
- Response caching
- Rate limiting

## Core Components

### AI Handler (`/includes/Core/AI.php`)
Main class for AI operations with methods:
- `generateText()` - Generate text content
- Caching system (1 hour cache duration)
- Rate limiting (5 requests per minute)

### API Endpoint (`/routes/api/ai.php`)
REST endpoint accepting POST requests with:
- Required: `prompt` parameter
- Optional: `model` parameter (default: gpt-3.5-turbo)

### Example Implementations (`/includes/AI/`)
Ready-to-use implementations:
- `ContentGenerator.php` - Article and product description generation

## Usage Examples

### Basic API Request
```javascript
fetch('/api/ai', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    prompt: 'Write a blog post about AI in web development',
    model: 'gpt-4'
  })
})
```

### Using ContentGenerator
```php
$generator = new ContentGenerator($apiKey);
$article = $generator->generateArticle(
  'Sustainable Energy', 
  'renewable, solar, wind', 
  'informative'
);
```

## Configuration
Set your OpenAI API key in `/config/openai.php`:
```php
<?php
return [
    'api_key' => 'your-api-key-here'
];
```

## Best Practices
- Cache responses when possible
- Implement client-side rate limiting
- Sanitize all prompts and responses
- Moderate generated content