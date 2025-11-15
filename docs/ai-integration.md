# AI Integration System Documentation

## Overview
The AI integration system provides a standardized way to connect with various AI providers through a unified interface. Currently supports OpenAI and Gemini providers.

## Current Implementation Status
- ✅ SEO Meta Generation: Fully implemented via `AIContentEnhancerController`
- 🔜 Content Tagging: Planned for next release
- 🔜 Summarization: Planned for next release  
- 🔜 Translation: Planned for next release

## Module Architecture

### Core Components
1. **AIContentEnhancerController** (`admin/controllers/AIContentEnhancerController.php`)
   - Handles content enhancement requests
   - Implements SEO meta generation
   - Routes requests to appropriate providers

2. **Provider Interface** (`includes/Providers/AIIntegrationProvider.php`)
   - Defines standard `process()` method
   - All providers must implement this interface

3. **Provider Implementations**
   - OpenAI: `includes/Providers/OpenAIProvider.php`
   - Gemini: `includes/Providers/GeminiProvider.php`

## Usage Instructions

### SEO Meta Generation
```php
$controller = new AIContentEnhancerController('openai', $config);
$meta = $controller->generateMeta(
    'Page Title', 
    'Page content goes here...',
    'en' // optional language code
);

// Returns:
// [
//   'meta_title' => '...',
//   'meta_description' => '...',
//   'keywords' => ['...', '...'],
//   'language' => 'en'
// ]
```

### Future Features
1. **Content Tagging**
```php
// Coming soon
$tags = $controller->generateTags($content);
```

2. **Summarization**  
```php
// Coming soon  
$summary = $controller->generateSummary($content);
```

3. **Translation**
```php
// Coming soon
$translation = $controller->translate($content, 'es');
```

## API Endpoints

### Core Endpoint
- `/api/ai-content` (POST)
- Accepts JSON payload:
```json
{
  "action": "generate_meta",
  "title": "Page Title",
  "content": "Page content...",
  "language": "en",
  "provider": "openai|gemini"
}
```

### Response Format
```json
{
  "status": "success",
  "data": {
    "meta_title": "...",
    "meta_description": "...",
    "keywords": ["..."]
  }
}
```

## Configuration
Provider settings are stored in `config/providers.json`:
```json
{
  "openai": {
    "api_key": "your-api-key",
    "model": "gpt-4",
    "temperature": 0.7,
    "max_tokens": 1000
  },
  "gemini": {
    "api_key": "your-api-key",
    "model": "gemini-pro",
    "temperature": 0.7,
    "max_output_tokens": 1000
  }
}
```

## Provider Integration

### Adding New Providers
1. Create new class implementing `AIIntegrationProvider`
2. Add configuration to `providers.json`
3. Update `AIContentEnhancerController` to support new provider

### Provider Specifications
| Feature       | OpenAI Provider | Gemini Provider |
|--------------|----------------|----------------|
| API Endpoint | `chat/completions` | `generateContent` |
| Auth Method  | Bearer Token   | URL Key Param  |
| Model Config | In POST body   | In URL         |
| Max Tokens   | `max_tokens`   | `max_output_tokens` |

## Error Handling
- Returns 500 status code for errors
- Error response format:
```json
{
  "status": "error",
  "message": "Error description"
}