# AI Suggestions Usage Guide

## Getting Started
The AI Suggestions system provides personalized content recommendations that can be integrated into:
- CMS content pages
- Email newsletters
- User dashboards

## Integration Methods

### 1. CMS Content Blocks
```php
// Example PHP integration
$suggestions = AISuggestions::getForUser($currentUser, [
    'limit' => 3,
    'content_type' => 'article'
]);

foreach ($suggestions as $suggestion) {
    echo "<div class='suggestion'>";
    echo "<h3>{$suggestion->title}</h3>";
    echo "<p>{$suggestion->description}</p>";
    echo "</div>";
}
```

### 2. JavaScript Widget
```javascript
// Load suggestions widget
const widget = new AISuggestionsWidget({
    container: '#suggestions-container',
    userId: currentUserId,
    onSelect: (suggestion) => {
        console.log('Selected:', suggestion);
    }
});
```

### 3. REST API Integration
```javascript
// Example API call
fetch('/api/v1/suggestions/content?limit=5', {
    headers: {
        'Authorization': `Bearer ${userToken}`
    }
})
.then(response => response.json())
.then(data => {
    // Process suggestions
});
```

## Configuration Options

### Personalization Settings
```php
// Configure suggestion weights
AISuggestions::configure([
    'content_relevance' => 0.7,
    'user_history' => 0.9,
    'popularity' => 0.3
]);
```

### Caching Behavior
```php
// Set cache TTL (in seconds)
AISuggestions::setCacheTTL(3600);
```

## Best Practices
1. Always include feedback mechanisms
2. Monitor suggestion performance
3. Regularly update training data
4. Test different recommendation algorithms