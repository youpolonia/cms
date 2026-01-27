<?php
return [
    "up" => function(\PDO $pdo): void {
        $stmt = $pdo->query("SHOW COLUMNS FROM system_settings LIKE 'active_theme'");
        if ($stmt->rowCount() === 0) {
            $pdo->exec("ALTER TABLE system_settings ADD COLUMN active_theme VARCHAR(50) DEFAULT 'default' AFTER maintenance_mode");
        }
        $pdo->exec("UPDATE system_settings SET active_theme = 'default' WHERE active_theme IS NULL OR active_theme = ''");
    },
    "down" => function(\PDO $pdo): void {
        $pdo->exec("ALTER TABLE system_settings DROP COLUMN active_theme");
    }
];
