# Notification Template System

## Overview
The Notification Template system provides a framework-free PHP implementation for loading and rendering notification templates from the database. It consists of two main components:
- `models/NotificationTemplate.php`: Handles database operations and template loading
- `includes/TemplateProcessor.php`: Processes template variables and rendering

## API Reference

### NotificationTemplate Class

#### `loadById(int $templateId): NotificationTemplate`
Loads a template from the database by ID.

**Parameters:**
- `$templateId`: The ID of the template to load

**Returns:**
- `NotificationTemplate` instance on success

**Throws:**
- `PDOException` on database errors
- `RuntimeException` if template not found

**Example:**
```php
try {
    $template = NotificationTemplate::loadById(123);
} catch (RuntimeException $e) {
    // Handle template not found
} catch (PDOException $e) {
    // Handle database error
}
```

#### `render(array $variables): array`
Renders the template with provided variables.

**Parameters:**
- `$variables`: Associative array of template variables

**Returns:**
- Array with keys:
  - `subject`: Rendered subject line
  - `body`: Rendered body content

**Throws:**
- `InvalidArgumentException` if required variables are missing
- `RuntimeException` for template processing errors

**Example:**
```php
$variables = [
    'username' => 'johndoe',
    'action_url' => 'https://example.com/confirm'
];

$rendered = $template->render($variables);
echo $rendered['subject']; // Rendered subject
echo $rendered['body'];    // Rendered body
```

## Error Handling Guidelines

1. **Template Loading Errors**:
   - Always wrap `loadById()` in try-catch blocks
   - Check for both database errors and missing templates

2. **Rendering Errors**:
   - Validate all required variables before rendering
   - Handle potential HTML escaping requirements

3. **Common Error Cases**:
   - Missing template (RuntimeException)
   - Missing variables (InvalidArgumentException)
   - Database connection issues (PDOException)

## Best Practices

1. **Template Design**:
   - Use clear variable names like `{{username}}` rather than `{{var1}}`
   - Document required variables in template metadata

2. **Performance**:
   - Consider caching frequently used templates
   - Batch load templates when possible

3. **Security**:
   - Always use prepared statements for database access
   - Escape output appropriately based on delivery channel