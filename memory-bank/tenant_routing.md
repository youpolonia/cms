# Tenant-Aware Routing System

## Overview
The routing system now supports multi-tenancy with:
- Automatic tenant detection (subdomain, path prefix, header)
- Tenant-specific route registration
- Backward compatibility with existing routes

## Tenant Detection
Tenants are detected in this order:
1. Subdomain (tenant1.example.com)
2. Path prefix (/tenant1/route)
3. X-Tenant-ID header

## Route Registration
### Global Routes (all tenants)
```php
$router->addRoute('GET', '/about', $handler);
```

### Tenant-Specific Routes
```php
// Explicit tenant ID
$router->addRoute('GET', '/dashboard', $handler, 'tenant1');

// Using path prefix helper
$router->addTenantRoute('GET', '/dashboard', $handler, 'tenant1');
// Maps to: /tenant1/dashboard
```

## Backward Compatibility
- Existing routes continue working as before
- Global routes are available to all tenants
- Tenant-specific routes only match for their tenant

## Middleware
The `TenantDetectionMiddleware` should be added early in the middleware stack:
```php
$router->addGlobalMiddleware(new TenantDetectionMiddleware());