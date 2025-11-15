### Migration Document: Create `status_transitions` Table

#### Purpose
Create the `status_transitions` table to log status transitions of content items across the CMS in a way fully compatible with FTP-deployable, framework-free architecture.

---

#### Schema (MySQL)

```sql
CREATE TABLE IF NOT EXISTS status_transitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(255) NOT NULL,
    entity_id BIGINT NOT NULL,
    from_status VARCHAR(255) NOT NULL,
    to_status VARCHAR(255) NOT NULL,
    transition_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    reason TEXT
);
```

#### Rollback

```sql
DROP TABLE IF EXISTS status_transitions;
```

---

#### PHP Migration (Procedural, No Frameworks)

```php
<?php

function create_status_transitions_table(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();

        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS status_transitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type VARCHAR(255) NOT NULL,
    entity_id BIGINT NOT NULL,
    from_status VARCHAR(255) NOT NULL,
    to_status VARCHAR(255) NOT NULL,
    transition_time DATETIME DEFAULT CURRENT_TIMESTAMP,
    reason TEXT
)
SQL;

        $pdo->exec($sql);
        return $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Migration failed: " . $e->getMessage());
        return false;
    }
}

function drop_status_transitions_table(PDO $pdo): bool {
    try {
        $pdo->beginTransaction();
        $pdo->exec("DROP TABLE IF EXISTS status_transitions");
        return $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Rollback failed: " . $e->getMessage());
        return false;
    }
}
?>
```

---

#### Testing Instructions

1. Call `create_status_transitions_table($pdo)` in a migration runner or test script.
2. Insert sample records and verify structure and data.
3. Call `drop_status_transitions_table($pdo)` to roll back.
4. Confirm table is removed.
5. Re-run to ensure idempotency.

---

#### Notes
- No frameworks, classes, autoloaders or `up()`/`down()` methods used.
- Follows CMS procedural architecture rules.
- Safe to deploy via FTP.