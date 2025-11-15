# Content Archival System Documentation

## Overview
The Content Archival System automatically archives expired content based on configured rules. Key components:

1. **ArchivalWorkflow** - Core logic for identifying and processing expired content
2. **TestableArchivalWorkflow** - Testing subclass with public method access
3. **ContentExpirationRules** - Configuration for content lifetime

## Workflow Methods
```php
protected static function getExpiredContent(): array
protected static function archiveContent(array $content): void  
protected static function logArchival(int $contentId): void
```

## Testing Approach
Tests use `TestableArchivalWorkflow` which extends the main class and exposes protected methods as public for testing.

## Configuration
Set expiration rules in `config/content_expiration.php`:
```php
return [
    'default_ttl' => 30, // days
    'content_types' => [
        'news' => 7,
        'blog' => 90
    ]
];
```

## Error Handling
- Invalid content throws `InvalidContentException`
- Database errors throw `ArchivalException`
- All errors logged to `storage/logs/archival.log`