# CompanyModel Implementation Plan

## Database Schema
```sql
CREATE TABLE `companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `logo_url` varchar(512) DEFAULT NULL,
  `website` varchar(512) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

## Model Class Structure
```php
<?php
// admin/models/Company.php
class Company {
    public static function create(array $data): int { /*...*/ }
    public static function update(int $id, array $data): bool { /*...*/ }
    public static function delete(int $id): bool { /*...*/ }
    public static function getById(int $id): ?array { /*...*/ }
    public static function getBySlug(string $slug): ?array { /*...*/ }
    public static function listAll(): array { /*...*/ }
    public static function validate(array $data): array { /*...*/ }
}
```

## API Routes
```php
// routes.php
$router->get('/api/companies', function() {
    return json_response(Company::listAll());
});

$router->get('/api/companies/{slug}', function($slug) {
    return json_response(Company::getBySlug($slug) ?? error_404();
});

$router->post('/api/companies', function() {
    $data = validate_input(Company::validate($_POST));
    return json_response(['id' => Company::create($data)]);
});

// ... additional routes
```

## Admin UI Integration
1. Add to admin navigation:
   - Companies (icon: building)
2. Create admin views:
   - /admin/views/companies/list.php
   - /admin/views/companies/edit.php
3. Add to permissions system

## Implementation Steps
1. Create database migration
2. Implement Company model class
3. Add API routes
4. Create admin UI components
5. Add documentation
6. Test all CRUD operations