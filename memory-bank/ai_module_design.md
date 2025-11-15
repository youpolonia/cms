# AIContentEnhancer Module Design

## Class Structure

```php
class AIContentEnhancer {
    private AIIntegrationProvider $provider;
    private array $config;
    
    public function __construct(string $providerName, array $config) {
        $this->provider = $this->createProvider($providerName, $config);
        $this->config = $config;
    }
    
    // Content enhancement methods
    public function generateSeoMeta(string $content): array {
        $prompt = "Generate SEO meta tags for: $content";
        return $this->processStandardRequest($prompt);
    }
    
    public function summarizeContent(string $content, int $length = 200): string {
        $prompt = "Summarize this in $length characters: $content";
        return $this->processStandardRequest($prompt);
    }
    
    // Other enhancement methods...
    
    private function processStandardRequest(string $prompt): array {
        return $this->provider->process([
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]);
    }
    
    private function createProvider(string $name, array $config): AIIntegrationProvider {
        return match($name) {
            'openai' => new OpenAIProvider($config),
            'gemini' => new GeminiProvider($config),
            default => throw new InvalidArgumentException("Unknown provider: $name")
        };
    }
}
```

## API Endpoints

1. `/api/ai/enhance` (POST)
```json
{
  "provider": "openai|gemini",
  "action": "seo|summarize|tag|translate",
  "content": "Text to enhance",
  "options": {
    "length": 200,
    "target_language": "es"
  }
}
```

2. `/api/ai/batch-enhance` (POST)
- For processing multiple content items

## Integration Points

1. **Content Editor**:
   - Add enhancement toolbar buttons
   - Real-time preview of enhancements

2. **Publishing Workflow**:
   - Automatic SEO generation
   - Content summarization for excerpts

3. **Batch Processing**:
   - Enhance existing content
   - Multi-language translation

## Error Handling

- Standardized error responses:
```json
{
  "status": "error",
  "code": "ERR_AI_QUOTA",
  "message": "API quota exceeded"
}
```

## Configuration

Extend `providers.json`:
```json
{
  "enhancements": {
    "default_provider": "openai",
    "seo_template": "Generate meta for: {content}",
    "summarize_length": 200
  }
}