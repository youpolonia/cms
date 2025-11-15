# TenantManager Service Documentation

## Overview
The `TenantManager` class provides multi-tenancy management capabilities for the CMS, including tenant registration, validation, quota management, and data isolation.

## Key Features
- Tenant registration with resource quotas
- Tenant validation and scope checking
- Resource quota management
- Data isolation through query scoping
- Comprehensive error handling and logging

## Usage Examples

### Registering a New Tenant
```php
try {
    $tenantId = TenantManager::registerTenant(
        'Acme Corp',
        'admin@acme.com',
        [
            'cpu' => 200,
            'memory' => 2048,
            'storage' => 20000,
            'requests' => 200000
        ]
    );
} catch (Exception $e) {
    // Handle registration error
}
```

### Validating a Tenant
```php
if (TenantManager::validateTenant($tenantId)) {
    // Tenant is valid and active
}
```

### Getting Tenant Details
```php
$tenant = TenantManager::getTenant($tenantId);
echo "CPU Usage: {$tenant['cpu_usage']}/{$tenant['cpu_quota']}";
```

### Checking Resource Quota
```php
if (TenantManager::checkQuota($tenantId, 'cpu', 50)) {
    // Quota available
}
```

### Scoping Queries to Tenant
```php
$query = TenantManager::scopeQuery($tenantId, 'content_pages');
$pages = DB::select($query);
```

### Deactivating a Tenant
```php
if (TenantManager::deactivateTenant($tenantId)) {
    // Tenant deactivated
}
```

## Error Handling
All methods throw exceptions on critical errors and log details to `logs/tenant_manager.log`. 

Common exceptions:
- `PDOException` for database errors
- `Exception` for business logic violations

## Logging
The service maintains detailed logs with timestamps and severity levels (INFO, ERROR). Logs can be found in:
```
/logs/tenant_manager.log
```

## Database Schema Requirements
The service requires these tables:
- `tenants` - Core tenant information
- `tenant_usage` - Resource usage tracking
- `tenant_settings` - Tenant configuration