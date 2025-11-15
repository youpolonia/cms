# SEO Toolkit Plugin Hooks

## Available Hooks

### `before_seo_analysis`
**Purpose:** Modify content before SEO analysis  
**Parameters:** 
- `string $content`: The page content to analyze  
**Returns:** 
- `string`: Modified content  
**Example:**
```php
add_hook('before_seo_analysis', function($content) {
    // Remove shortcodes before analysis
    return preg_replace('/\[.*?\]/', '', $content);
});
```

### `after_seo_analysis` 
**Purpose:** Modify analysis results  
**Parameters:**
- `array $analysis`: The SEO analysis results  
**Returns:**
- `array`: Modified analysis  
**Example:**
```php
add_hook('after_seo_analysis', function($analysis) {
    // Add custom scoring metric
    $analysis['custom_score'] = calculate_custom_score($analysis);
    return $analysis;
});
```

### `seo_meta_generation`
**Purpose:** Modify generated meta tags  
**Parameters:**
- `array $meta`: Default meta tags  
**Returns:**
- `array`: Modified meta tags  
**Example:**
```php
add_hook('seo_meta_generation', function($meta) {
    // Add OpenGraph tags
    $meta['og:title'] = $meta['title'];
    $meta['og:description'] = $meta['description'];
    return $meta;
});
```

## Implementation Notes

1. Hooks are applied using the `apply_hooks()` function
2. Plugins must register hooks using `add_hook()`
3. Multiple hooks can be registered for the same point
4. Hooks are executed in registration order
5. Each hook receives the previous hook's return value