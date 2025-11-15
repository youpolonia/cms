# CMS Hook System

## Core Concepts

The hook system allows modifying CMS behavior without editing core files. There are two main types:

1. **Actions**: Points where plugins can execute code
2. **Filters**: Points where plugins can modify data

## Basic Usage

### Adding Actions
```php
add_action('hook_name', 'callback_function', priority, accepted_args);
```

Example:
```php
add_action('template_header', function() {
    echo '<!-- Plugin Header Content -->';
}, 10);
```

### Applying Filters
```php
$value = apply_filters('filter_name', $value, ...$args);
```

Example:
```php
add_filter('page_title', function($title) {
    return $title . ' | My Site';
}, 5);
```

## Common Core Hooks

### Template Hooks
- `template_header`: Runs in <head>
- `template_footer`: Runs before </body>
- `before_content`: Runs before main content
- `after_content`: Runs after main content

### Admin Hooks
- `admin_menu`: Add admin menu items
- `admin_init`: Runs during admin initialization
- `admin_footer`: Runs in admin footer

### Content Hooks
- `the_content`: Filters post content
- `excerpt`: Filters post excerpts
- `title`: Filters post titles

## Advanced Patterns

### Creating Custom Hooks
```php
// Action example
do_action('custom_action', $arg1, $arg2);

// Filter example
$value = apply_filters('custom_filter', $value, $arg1);
```

### Priority System
Hooks execute in priority order (default 10). Lower numbers execute earlier:
```php
add_action('hook', 'early_func', 5);
add_action('hook', 'default_func'); // priority 10
add_action('hook', 'late_func', 15);
```

### Removing Hooks
```php
remove_action('hook', 'callback', priority);
remove_filter('hook', 'callback', priority);
```

## Best Practices

1. **Document hooks**: Always document available hooks in your plugin
2. **Unique prefixes**: Use plugin prefix for custom hooks
3. **Performance**: Avoid expensive operations in frequently-called hooks
4. **Security**: Validate all hook inputs and escape outputs