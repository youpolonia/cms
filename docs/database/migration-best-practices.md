# Database Migration Best Practices

## Safe Migration Patterns

### 1. Table Creation
Always verify table doesn't exist before creating:
```php
public static function migrate(\PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'table_name'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("CREATE TABLE table_name (...)");
        }
        return true;
    } catch (\PDOException $e) {
        error_log("Migration failed: " . $e->getMessage());
        return false;
    }
}
```

### 2. Foreign Key Constraints
Verify referenced tables exist first:
```php
public static function migrate(\PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SHOW TABLES LIKE 'referenced_table'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE table_name ADD CONSTRAINT ...");
        }
        return true;
    } catch (\PDOException $e) {
        error_log("Migration failed: " . $e->getMessage());
        return false;
    }
}
```

### 3. Column Modifications
Check column exists before modifying:
```php
public static function migrate(\PDO $pdo): bool {
    try {
        $stmt = $pdo->query("SHOW COLUMNS FROM table_name LIKE 'column_name'");
        if ($stmt->rowCount() > 0) {
            $pdo->exec("ALTER TABLE table_name MODIFY column_name ...");
        }
        return true;
    } catch (\PDOException $e) {
        error_log("Migration failed: " . $e->getMessage());
        return false;
    }
}
```

## Migration Checklist

1. [ ] Verify table doesn't exist before creation
2. [ ] Check dependency tables exist before FKs  
3. [ ] Include proper error handling and transactions
4. [ ] Test migration rollback
5. [ ] Consider batch execution timing
6. [ ] Document table/index purposes
7. [ ] Follow naming convention: YYYYMMDD_phaseX_description.php
8. [ ] Validate against test data

## Example Implementation
See: `database/migrations/20250611_phase9_tenant_isolation.php`