# Database Migration Best Practices

## Standardized Naming Convention
1. File format: `YYYYMMDD_phaseX_description.php` (e.g. `20250611_phase9_tenant_isolation.php`)
2. Class name: `PascalCase` matching filename (without .php extension)

## Safe Migration Patterns

### 1. Table Existence Check
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

### 2. Foreign Key Safety
```php
$stmt = $pdo->query("SHOW TABLES LIKE 'related_table'");
if ($stmt->rowCount() > 0) {
    $pdo->exec("ALTER TABLE table_name ADD CONSTRAINT fk_name 
               FOREIGN KEY (related_id) REFERENCES related_table(id) ON DELETE CASCADE");
}
```

### 3. Column/Index Safety
```php
$stmt = $pdo->query("SHOW COLUMNS FROM table_name LIKE 'column_name'");
if ($stmt->rowCount() === 0) {
    $pdo->exec("ALTER TABLE table_name ADD COLUMN column_name ...");
    $pdo->exec("CREATE INDEX idx_name ON table_name (column_name)");
}
```

### 4. Rollback Safety
```php
public static function rollback(\PDO $pdo): bool {
    try {
        $pdo->exec("DROP TABLE IF EXISTS table_name");
        return true;
    } catch (\PDOException $e) {
        error_log("Rollback failed: " . $e->getMessage());
        return false;
    }
}
```

## Implementation Guidelines
1. Use [`StandardizedMigrationTemplate.php`](database/migrations/StandardizedMigrationTemplate.php)
2. Test with `test()` method before deployment
3. Document all schema changes in [`decisionLog.md`](memory-bank/decisionLog.md)
4. Follow naming conventions from [`db_migration_rules.md`](memory-bank/db_migration_rules.md)