# SEO Toolkit Quick Start Guide

## Installation
The SEO Toolkit is included by default in the CMS. No additional installation required.

## Basic Usage

### 1. Adding Meta Tags
```php
// In your page template:
$seoToolkit = new SEOToolkit();
$metaTags = $seoToolkit->generateMetaTags([
    'title' => 'My Page Title',
    'description' => 'Brief page description'
]);

// Output in <head> section:
foreach ($metaTags as $name => $content) {
    echo "<meta name=\"$name\" content=\"$content\">";
}
```

### 2. Analyzing Content
```php
$content = "Your page content here...";
$analysis = $seoToolkit->analyzeContent($content);

// Display results to content authors
echo "Readability Score: " . $analysis['readabilityScore'];
echo "Suggestions: " . $analysis['suggestions'];
```

### 3. Using the Admin Interface
1. Navigate to Admin → SEO Toolkit
2. Paste your content in the analyzer
3. Review suggestions and apply improvements
4. Generate or edit meta tags as needed

## Common Tasks

### Adding Schema Markup
```php
$seoToolkit->registerFeature('schemaMarkup', function($pageData) {
    return [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $pageData['title']
    ];
});
```

### Handling Errors
```php
try {
    $seoToolkit->analyzeContent($veryLongContent);
} catch (SEOToolkitException $e) {
    // Show user-friendly message
    echo "SEO Analysis Error: " . $e->getUserMessage();
}
```

## Troubleshooting

**Problem:** Analysis takes too long  
**Solution:** Set a timeout:
```php
$seoToolkit->setTimeout(3000); // 3 seconds
```

**Problem:** Getting CSRF errors  
**Solution:** Ensure your form includes:
```html
<input type="hidden" name="_token" value="<?= csrf_token() ?>">
```

## Next Steps
- Explore advanced features in [`seo-toolkit.md`](seo-toolkit.md)
- Create custom SEO plugins
- Configure caching and rate limiting