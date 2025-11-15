# Database Configuration Error Analysis

## Root Cause
1. **Mismatched Configuration Formats**:
   - `includes/config.php` returns configuration as an array (e.g. `'db_host' => 'localhost'`)
   - `admin/includes/security.php` expects constants (e.g. `DB_HOST`)

2. **Error Flow**:
   - `admin/dashboard.php` includes `security.php`
   - `security.php` includes `config.php` but doesn't use its array values
   - `security.php` tries to use undefined constants (DB_HOST, DB_NAME, etc.)

## Recommended Solutions

### Option 1: Modify security.php to use config array
```php
$config = require __DIR__ . '/../../includes/config.php';
$db = new PDO(
    'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
    $config['db_user'],
    $config['db_pass']
);
```

### Option 2: Update config.php to define constants
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms_database');
define('DB_USER', 'cms_user');
define('DB_PASS', 'secure_password_here');
```

## Additional Findings
- The system has multiple database configuration approaches:
  - `config/database.php` (used by auth.php)
  - `includes/config.php` (used by security.php)
- Consider standardizing on one configuration method