# API Documentation - Migration System

## 1. Migration System API Endpoints

### `/migrate/apply`
- **Method**: POST
- **Purpose**: Apply pending database migrations
- **Parameters**:
  - `batch_size` (optional): Max migrations to apply (default: all)
- **Response**:
  ```json
  {
    "status": "success|error",
    "applied": ["migration1.php", "migration2.php"],
    "errors": []
  }
  ```

### `/migrate/revert`
- **Method**: POST  
- **Purpose**: Revert last applied migration batch
- **Response**:
  ```json
  {
    "status": "success|error",
    "reverted": ["migration2.php"],
    "errors": []
  }
  ```

## 2. Version Tracking System

### Methods
- `ensureTableExists()` - Creates version tracking table
- `recordVersion(version)` - Logs applied migration
- `getAppliedVersions()` - Returns migration history

## 3. Framework-Free PHP Implementation

- Pure PHP 8.1+ with no framework dependencies
- Static method pattern (`Migration::apply()`)
- Direct PDO usage for database operations
- File-based migration storage (`/migrations/`)

## 4. Error Handling

**Response Formats**:
```json
{
  "error": {
    "code": "MIGRATION_FAILED",
    "message": "Detailed error message",
    "details": {}
  }
}
```

## 5. Security Considerations

- Input validation for all web endpoints
- Checksum verification for migration files
- Limited endpoint accessibility (IP whitelisting)
- No direct SQL parameter interpolation

## 6. Web-Accessible Testing

- `/migrate/test` - Validate system readiness
- `/migrate/status` - Current migration state