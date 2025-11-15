# Laravel Pattern Cleanup Priorities

## Critical (Immediate Action Required)
1. [`includes/Models/BaseModel.php`](includes/Models/BaseModel.php:5) - Replace Eloquent extension with pure PHP implementation
2. [`includes/Database/TenantScope.php`](includes/Database/TenantScope.php:5) - Remove Laravel scope dependency

## High Priority
1. Migration classes using Laravel syntax patterns
2. Service provider implementations

## Medium Priority
1. Facade usage patterns
2. Eloquent relationship methods