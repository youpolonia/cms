# Development Mode (DEV_MODE)

## Overview
The development mode is a simple flag that helps identify when the system is running in a development environment.

## Configuration
Set in `includes/config.php`:
```php
'dev_mode' => true, // Set to false in production
```

## Visual Indicator
When enabled, a red "DEV MODE" badge appears in the top-right corner of the admin interface.

## Current Behavior
- Only shows visual indicator
- No other functional differences

## Best Practices
- Always disable in production (`'dev_mode' => false`)
- Can be used to conditionally enable debug features
- Consider adding IP restrictions in future

## Future Enhancements
- Could enable debug logging
- Could show additional diagnostic information
- Could disable caching mechanisms

# InputValidator Documentation

## API Reference

### `validateEmail(string $email): bool`
Validates an email address format.
```php
InputValidator::validateEmail('test@example.com'); // true
InputValidator::validateEmail('invalid-email'); // false
```

### `validateUsername(string $username): bool`
Validates username format (3-20 chars, alphanumeric + underscore).
```php
InputValidator::validateUsername('valid_user123'); // true
InputValidator::validateUsername('no spaces'); // false
```

### `validatePassword(string $password): bool`
Validates password strength (min 8 chars, at least 1 letter and 1 number).
```php
InputValidator::validatePassword('Secure123'); // true
InputValidator::validatePassword('weak'); // false
```

### `sanitizeText(string $text): string`
Sanitizes text input (strips tags, trims, escapes special chars).
```php
InputValidator::sanitizeText('<script>alert(1)</script>'); // "&lt;script&gt;alert(1)&lt;/script&gt;"
```

### `sanitizeHTML(string $html): string`
Sanitizes HTML input (allows basic HTML tags).
```php
InputValidator::sanitizeHTML('<p>Safe</p><script>alert(1)</script>'); // "<p>Safe</p>"
```

### `isNumeric(mixed $input): bool`
Checks if input is numeric.
```php
InputValidator::isNumeric('123'); // true
InputValidator::isNumeric('abc'); // false
```

### `checkCSRF(string $token): bool`
Validates CSRF token against session.
```php
InputValidator::checkCSRF($_POST['csrf_token']);
```

## Form Integration Examples

### Basic Form Validation
```php
$email = $_POST['email'] ?? '';
if (!InputValidator::validateEmail($email)) {
    throw new InvalidInputException('Invalid email format');
}
$cleanEmail = InputValidator::sanitizeText($email);
```

### HTML Content Processing
```php
$content = $_POST['content'] ?? '';
$cleanContent = InputValidator::sanitizeHTML($content);
```

## Security Best Practices

1. Always validate before sanitizing
2. Use CSRF protection for all form submissions
3. Store passwords securely (not shown here - use password_hash())
4. Escape output according to context (HTML, SQL, etc.)
5. Use strict type checking where possible

## Error Handling Patterns

### Validation Error Handling
```php
try {
    if (!InputValidator::validateUsername($username)) {
        throw new ValidationException('Invalid username format');
    }
} catch (ValidationException $e) {
    // Log and show user-friendly error
    error_log($e->getMessage());
    $errors[] = 'Please enter a valid username (3-20 chars, letters/numbers/underscores)';
}
```

## CSRF Protection Implementation

1. Generate token in session:
```php
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
```

2. Include in forms:
```html
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
```

3. Validate on submission:
```php
if (!InputValidator::checkCSRF($_POST['csrf_token'])) {
    throw new SecurityException('CSRF token validation failed');
}