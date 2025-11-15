# AI Translator Assistant Documentation

## Overview
The AI Translator Assistant provides automated content translation capabilities using the CMS's core TranslationService.

## Features
- Content translation between supported languages
- Language detection
- Support for 50+ languages (see supported languages list)

## Integration Points
1. **Content Pipeline**: Automatically translates content during publishing
2. **Editor Tools**: Provides in-editor translation suggestions
3. **API Endpoints**: `/api/translate` and `/api/detect-language`

## Configuration
Add to `config/assistants.php`:
```php
'translator' => [
    'enabled' => true,
    'default_target' => 'en',
    'cache_ttl' => 3600
]
```

## Usage Example
```php
$translator = new AssistantMain();
$translated = $translator->translate($content, 'es');
```

## Supported Languages
See TranslationService documentation for current list.

## Version History
- 1.0.0 (2025-06-14): Initial release