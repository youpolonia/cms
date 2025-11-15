# NotificationTemplate Documentation

## Implementation Formats

### Array Implementation
```php
$template = new NotificationTemplate([
    'variables' => ['name', 'date'],
    'content' => "Hello {{name}}, your appointment is on {{date}}"
]);
```

### JSON Implementation
```php
$json = '{
    "variables": ["name", "date"],
    "content": "Hello {{name}}, your appointment is on {{date}}"
}';
$template = NotificationTemplate::fromJson($json);
```

## Variable Substitution

- Syntax: `{{variable_name}}`
- Variables are validated during `render()` call
- Missing variables throw `MissingVariableException`

## Edge Case Handling

1. Missing Variables:
```php
try {
    $template->render(['name' => 'John']);
} catch (MissingVariableException $e) {
    // Handle missing 'date' variable
}
```

2. Static Caching:
```php
// First call processes template
$template->render($vars);

// Subsequent calls use cached version
$template->render($vars); 
```

## Examples

### Basic Usage
```php
$template = new NotificationTemplate([
    'variables' => ['username', 'login_time'],
    'content' => "User {{username}} logged in at {{login_time}}"
]);

echo $template->render([
    'username' => 'johndoe',
    'login_time' => date('Y-m-d H:i:s')
]);
```

### JSON Example
```php
$json = '{
    "variables": ["order_id", "status"],
    "content": "Order #{{order_id}} is now {{status}}"
}';

$template = NotificationTemplate::fromJson($json);
echo $template->render([
    'order_id' => 12345,
    'status' => 'shipped'
]);