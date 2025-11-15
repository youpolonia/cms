# Automated API Documentation Generation

## Setup Instructions

1. Install required package:
```bash
composer require darkaonline/l5-swagger
```

2. Publish configuration:
```bash
php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
```

3. Configure in `config/l5-swagger.php`:
```php
'default' => 'default',
'documentations' => [
    'default' => [
        'api' => [
            'title' => 'CMS API',
        ],
        'routes' => [
            'api' => 'api/documentation',
            'docs' => 'docs/api',
        ],
    ],
],
```

4. Generate documentation:
```bash
php artisan l5-swagger:generate
```

## Scheduled Generation
Add to `app/Console/Kernel.php`:
```php
$schedule->command('l5-swagger:generate')->daily();
```

## Viewing Documentation
Access the UI at:
```
http://localhost:8000/api/documentation
```

## Troubleshooting
- Clear cache if endpoints don't update:
```bash
php artisan cache:clear
```
- Regenerate after API changes:
```bash
php artisan l5-swagger:generate