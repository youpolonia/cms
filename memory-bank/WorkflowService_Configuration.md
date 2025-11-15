# WorkflowService Configuration Guide

## Core Settings

### Debug Mode
```php
// includes/Services/WorkflowService.php
const DEBUG_MODE = false; // Set to true for development
```

### Audit Logging
```php
// includes/Services/WorkflowService.php
const AUDIT_VERBOSITY = [
    'transitions' => true,
    'errors' => true,
    'debug' => false
];
```

## Tenant Isolation
```php
// database/migrations/Migration_0001_TenantIsolation.php
// All workflow tables automatically include tenant_id column
// Set isolation level in WorkflowService:
const ISOLATION_LEVEL = 'strict'; // Options: strict, relaxed, none
```

## Performance Tuning
```php
// Maximum batch size for bulk operations
const MAX_BATCH_SIZE = 100;

// Transaction timeout (seconds)
const TX_TIMEOUT = 30;
```

## Required Permissions
```ini
; Required file permissions
includes/Services/WorkflowService.php = 644
database/migrations/*.php = 644
public/api/workflow/*.php = 755