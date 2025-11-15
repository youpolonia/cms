# Migration Standards

## File Structure
- All migrations must be procedural PHP files
- File naming: `YYYY_phaseXX_description.php` (e.g. `2025_phase10_user_roles.php`)
- Located in `database/migrations/` directory

## Required Functions
Each migration must implement exactly two functions:

```php
function apply_migration(PDO $pdo): void {
    // Migration logic here
}

function rollback_migration(PDO $pdo): void {
    // Rollback logic here
}
```

## Prohibited Patterns
- No class declarations
- No namespace declarations
- No error logging (`error_log()`)
- No test endpoint creation
- No CLI-specific code

## Execution Requirements
- Migrations must run silently (no output)
- Must use PDO for database operations
- Must be idempotent (safe to run multiple times)
- Must include complete rollback functionality

## Validation
Use the validator script to check compliance:
```php
require_once 'database/migrations/validator.php';
$results = validate_all_migrations(__DIR__);
```

## Best Practices
- Keep migrations focused on schema changes only
- Use transactions where possible
- Document complex operations with comments
- Test migrations thoroughly before deployment