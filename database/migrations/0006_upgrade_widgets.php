<?php
declare(strict_types=1);

/**
 * Migration: Upgrade widgets table
 * Adds: icon, description, visibility, cache_ttl, version, created_by, updated_at
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // Add icon column (emoji or icon class)
        if (!$this->columnExists($pdo, 'widgets', 'icon')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN icon VARCHAR(50) NULL AFTER name");
        }

        // Add description column
        if (!$this->columnExists($pdo, 'widgets', 'description')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN description VARCHAR(500) NULL AFTER slug");
        }

        // Add visibility column (who can see this widget)
        if (!$this->columnExists($pdo, 'widgets', 'visibility')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN visibility ENUM('all','logged_in','logged_out','admin') DEFAULT 'all' AFTER is_active");
        }

        // Add cache_ttl column (0 = no cache)
        if (!$this->columnExists($pdo, 'widgets', 'cache_ttl')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN cache_ttl INT UNSIGNED DEFAULT 0 AFTER visibility");
        }

        // Add version column for tracking changes
        if (!$this->columnExists($pdo, 'widgets', 'version')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN version INT UNSIGNED DEFAULT 1 AFTER cache_ttl");
        }

        // Add created_by column
        if (!$this->columnExists($pdo, 'widgets', 'created_by')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN created_by INT UNSIGNED NULL AFTER version");
        }

        // Add updated_at column
        if (!$this->columnExists($pdo, 'widgets', 'updated_at')) {
            $pdo->exec("ALTER TABLE widgets ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }

        // Add indexes for better query performance
        try {
            $pdo->exec("CREATE INDEX idx_widgets_area ON widgets(area)");
        } catch (\PDOException $e) {
            // Index may already exist
        }

        try {
            $pdo->exec("CREATE INDEX idx_widgets_type ON widgets(type)");
        } catch (\PDOException $e) {
            // Index may already exist
        }

        try {
            $pdo->exec("CREATE INDEX idx_widgets_active ON widgets(is_active)");
        } catch (\PDOException $e) {
            // Index may already exist
        }
    }

    public function down(\PDO $pdo): void
    {
        $columns = ['icon', 'description', 'visibility', 'cache_ttl', 'version', 'created_by', 'updated_at'];
        foreach ($columns as $col) {
            if ($this->columnExists($pdo, 'widgets', $col)) {
                $pdo->exec("ALTER TABLE widgets DROP COLUMN {$col}");
            }
        }

        // Drop indexes (ignore errors if they don't exist)
        try {
            $pdo->exec("DROP INDEX idx_widgets_area ON widgets");
            $pdo->exec("DROP INDEX idx_widgets_type ON widgets");
            $pdo->exec("DROP INDEX idx_widgets_active ON widgets");
        } catch (\PDOException $e) {
            // Indexes may not exist
        }
    }

    private function columnExists(\PDO $pdo, string $table, string $column): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (bool)$stmt->fetchColumn();
    }
};
