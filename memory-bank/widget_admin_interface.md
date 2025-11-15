# Widget Admin Interface Documentation

## 1. Overview of Widget Settings Architecture

The widget settings system provides CRUD operations for managing widget configurations. Key components:

- **WidgetSettingsController**: Handles HTTP requests and responses
- **WidgetSettings Model**: Contains business logic for widget operations
- **widget_settings Table**: Stores all widget configurations
- **Tenant Isolation**: All operations are tenant-scoped

## 2. Database Schema Details

```sql
CREATE TABLE widget_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    widget_type VARCHAR(50) NOT NULL,
    config_json JSON NOT NULL,
    tenant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_widget_settings_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
```

## 3. Security Implementation Notes

### Input Sanitization
- Implemented in `includes/SecurityHelper.php`
- Methods:
  - `sanitizeInput()`: Handles string, email, URL, int, float sanitization
  - `validateInput()`: Type-specific validation with strict checks

### CSRF Protection
- Middleware: `api/middleware/CsrfMiddleware.php`
- Features:
  - Token generation/validation
  - Automatic token rotation
  - Route exclusions for GET requests
  - Secure cookie attributes

### Session Authentication
- File: `admin/includes/auth.php`
- Security measures:
  - 30-minute idle timeout
  - Secure session regeneration
  - Role-based access control
  - Password hashing with `password_verify()`

## 4. API Endpoints Reference

| Endpoint | Method | Description | Parameters |
|----------|--------|-------------|------------|
| `/widget-settings` | POST | Create new widget | `type`, `config_json` |
| `/widget-settings/{id}` | GET | Get widget by ID | - |
| `/widget-settings` | GET | List all widgets | - |
| `/widget-settings/{id}` | PUT | Update widget | `type`, `config_json` |
| `/widget-settings/{id}` | DELETE | Delete widget | - |

## 5. Usage Examples

### Creating a Widget
```php
$response = $httpClient->post('/widget-settings', [
    'type' => 'weather',
    'config_json' => json_encode([
        'location' => 'London',
        'units' => 'metric'
    ])
]);
```

### Updating a Widget
```php
$response = $httpClient->put('/widget-settings/123', [
    'type' => 'weather',
    'config_json' => json_encode([
        'location' => 'Paris',
        'units' => 'imperial'
    ])
]);
```

## 6. Troubleshooting Guide

### Common Issues

**Widget not saving**
- Verify required fields (`type`) are provided
- Check JSON validity of `config_json`
- Confirm tenant/user permissions

**Widget not loading**
- Verify widget ID exists
- Check tenant scope matches
- Review error logs in `admin/logs/widget_errors.log`

**Permission denied**
- Confirm user has 'widget_admin' role
- Check session authentication
- Verify CSRF token is included for non-GET requests