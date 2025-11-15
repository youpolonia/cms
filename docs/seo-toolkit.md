# SEO Toolkit Module

## Overview
The SEO Toolkit provides comprehensive SEO management features including:
- Meta tag generation and management
- AI-powered content analysis
- Plugin system for extending functionality
- Built-in error handling and logging
- CSRF protection for all API endpoints

## Core Features

### Meta Tag Management
```php
// Example: Generating meta tags with error handling
try {
    $seoToolkit = new SEOToolkit();
    $metaTags = $seoToolkit->generateMetaTags([
        'title' => 'Page Title',
        'description' => 'Page description',
        'keywords' => 'keyword1, keyword2'
    ]);
} catch (SEOToolkitException $e) {
    error_log("SEO Toolkit Error: " . $e->getMessage());
    // Fallback to default meta tags
    $metaTags = [
        'title' => 'Default Title',
        'description' => 'Default Description'
    ];
}
```

### Content Analysis
```php
// Example: Analyzing content with timeout handling
$analysis = $seoToolkit->analyzeContent($content, [
    'timeout' => 5000, // 5 second timeout
    'retry' => 2 // Retry failed requests twice
]);

// Returns structured analysis or throws SEOToolkitException
// ['keywordDensity' => float, 'readabilityScore' => int, 'suggestions' => string]
```

### Plugin System
Plugins can extend SEO functionality by:
- Modifying meta tags
- Adding new schema markup
- Extending sitemap generation
- Registering custom analysis modules

```php
// Example plugin registration with error handling
$seoToolkit->registerFeature('schemaMarkup', function($pageData) {
    try {
        // Return schema.org JSON-LD
        return generateSchemaMarkup($pageData);
    } catch (Exception $e) {
        error_log("Schema Markup Error: " . $e->getMessage());
        return null; // Graceful fallback
    }
}, [
    'priority' => 10, // Execution priority
    'required' => false // Optional feature
]);
```

## API Reference

### `/admin/seo/seo-api.php`

#### generateMetaTags(keywords: string): object
Generates SEO meta tags using AI.

**Request Headers:**
- `X-CSRF-Token`: Required for all requests
- `X-Requested-With`: XMLHttpRequest

**Response:**
```json
{
    "title": "Generated Title",
    "description": "Generated Description",
    "keywords": "Original or Generated Keywords"
}
```

**Error Responses:**
```json
{
    "error": "Invalid CSRF token",
    "code": 403
}
```

#### analyzeContent(content: string): object
Analyzes content for SEO improvements.

**Rate Limited:** 10 requests/minute

**Response:**
```json
{
    "keywordDensity": 3.2,
    "readabilityScore": 78,
    "suggestions": "Consider adding more transition words...",
    "warnings": ["Low image alt text count"]
}
```

**Error Responses:**
```json
{
    "error": "Content too long (max 10000 chars)",
    "code": 400
}
```

## Frontend Usage

The Vue component provides an intuitive interface with built-in error handling:

```vue
<template>
  <seo-toolkit
    :initial-meta="pageMeta"
    @error="handleError"
    @loading="setLoading"
  />
</template>

<script>
export default {
  methods: {
    handleError(error) {
      // Display user-friendly error message
      this.$toast.error(error.message || 'SEO operation failed');
    },
    setLoading(isLoading) {
      // Show/hide loading indicator
    }
  }
}
</script>
```

## Configuration

```php
// config/seo-toolkit.php
return [
    'cache_ttl' => 3600, // 1 hour cache
    'rate_limit' => [
        'analyze' => '10/minute',
        'generate' => '5/minute'
    ],
    'logging' => [
        'enabled' => true,
        'level' => 'warning' // error, warning, info
    ]
];
```

## Error Handling

The SEO Toolkit implements multiple error handling layers:

1. **Input Validation** - All API inputs are validated
2. **CSRF Protection** - Required for all modifying operations
3. **Rate Limiting** - Prevents abuse of AI features
4. **Timeout Handling** - For long-running operations
5. **Graceful Fallbacks** - When features fail

Common error scenarios:
- Network failures (retry automatically)
- Invalid inputs (show user-friendly messages)
- API timeouts (fallback to cached results)

## Plugin Development

To create an SEO plugin:

1. Implement the required interface methods with error handling
2. Register with the SEO Toolkit including priority
3. Handle feature callbacks gracefully

Example plugin structure:
```php
class MySeoPlugin implements SEOToolkitPluginInterface {
    public function modifyMetaTags(array $metaTags, array $pageData): array {
        try {
            // Validate inputs
            if (empty($pageData['id'])) {
                throw new InvalidArgumentException('Missing page ID');
            }
            
            // Modify meta tags
            return array_merge($metaTags, [
                'og:title' => $metaTags['title']
            ]);
        } catch (Exception $e) {
            error_log("Meta Tag Modification Error: " . $e->getMessage());
            return $metaTags; // Return original on error
        }
    }
    
    public function getSupportedFeatures(): array {
        return ['metaTags', 'schemaMarkup'];
    }
}
```

Register your plugin:
```php
$seoToolkit->registerPlugin(new MySeoPlugin(), [
    'priority' => 50,
    'config' => [...]
]);
```