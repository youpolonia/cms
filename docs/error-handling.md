# Error Handling Patterns

## Database Operations

The CMS uses PDO for database operations with the following error handling approaches:

### 1. Transaction Pattern
Used for atomic operations (e.g., `ContentFields::reorderFields()`):
```php
$this->db->beginTransaction();
try {
    // Multiple database operations
    return $this->db->commit();
} catch (Exception $e) {
    $this->db->rollBack();
    return false;
}
```

### 2. Implicit PDO Error Handling
Most methods rely on PDO's error handling:
- By default, PDO throws `PDOException` on errors
- Callers should handle these exceptions
- Methods typically return:
  - `true`/`false` for write operations
  - Data arrays for read operations
  - `false` on failure

### 3. Common Error Scenarios
- **Duplicate machine names**: Will cause PDOException on unique constraint violation
- **Invalid field types**: May cause database errors
- **Missing references**: Foreign key violations will throw exceptions

## Best Practices
1. Always wrap database calls in try/catch blocks
2. Use transactions for multi-step operations
3. Check return values from write operations
4. Handle specific exception types when possible:
```php
try {
    // Database operation
} catch (PDOException $e) {
    // Handle database errors
    error_log($e->getMessage());
    return false;
}
```

## Error Reporting
- Errors are logged to PHP's error log
- API methods should return appropriate HTTP status codes
- Admin UI should display user-friendly error messages