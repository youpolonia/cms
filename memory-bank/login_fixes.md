# Admin Login Flow Fixes

## Session Validation Fix
1. Update dashboard.php to check `$_SESSION['authenticated']` instead of user_id:
```php
if (empty($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit;
}
```

## CSRF Protection
1. Add to login.php:
```php
// After session_start()
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

2. Update login form:
```html
<input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
```

3. Add validation to auth.php:
```php
if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    exit('Invalid CSRF token');
}
```

## Credential Management
1. Create config file at `config/admin_credentials.php`:
```php
<?php
return [
    'admin' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
];
```

2. Update auth.php to use config:
```php
$adminUsers = require __DIR__ . '/../config/admin_credentials.php';
```

## Error Display
1. Update login.php to show errors:
```php
<?php if (!empty($_GET['error'])): ?>
<div class="error-message">
    <?= match($_GET['error']) {
        'invalid_credentials' => 'Invalid username or password',
        'rate_limit' => 'Too many attempts - try again later',
        default => 'Login error'
    } ?>
</div>
<?php endif; ?>