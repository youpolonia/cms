# Configuration Reference

## Core Configuration Files

### database.php
```php
return [
    'host' => 'localhost',     // Database server
    'name' => 'cms_database',  // Database name
    'user' => 'cms_user',      // Database username
    'pass' => 'secure_password', // Database password
    'port' => 3306,            // Database port
    'charset' => 'utf8mb4'     // Character set
];
```

### auth.php
```php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
    ],
    // ...
];
```

### mail.php
```php
return [
    'default' => 'smtp',
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST'),
            'port' => env('MAIL_PORT'),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ],
    ],
    // ...
];
```

## Important Environment Variables
```
APP_ENV=production
APP_KEY=base64:... (32 character random string)
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=cms_database
DB_USER=cms_user
DB_PASS=secure_password
MAIL_MAILER=smtp
MAIL_HOST=mail.example.com
MAIL_PORT=587
MAIL_USERNAME=user@example.com
MAIL_PASSWORD=password
```

## Configuration Best Practices
1. Always set APP_ENV=production in production
2. Generate a unique APP_KEY for each deployment
3. Disable APP_DEBUG in production
4. Use environment variables for sensitive data
5. Test email configuration before going live