<?php
/**
 * Migration: Upgrade content_blocks table
 * Adds: category, cache_ttl, created_by, version, position, updated_at
 */

require_once __DIR__ . '/abstractmigration.php';

class Migration_0005_upgrade_content_blocks_table extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // === CONTENT_BLOCKS TABLE UPGRADES ===

        // Add category column (header, footer, sidebar, global)
        if (!$this->columnExists($db, 'content_blocks', 'category')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN category ENUM('header','footer','sidebar','global','uncategorized') DEFAULT 'uncategorized' AFTER type");
        }

        // Add cache_ttl column (in seconds, 0 = no cache)
        if (!$this->columnExists($db, 'content_blocks', 'cache_ttl')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN cache_ttl INT UNSIGNED DEFAULT 0 AFTER category");
        }

        // Add created_by column
        if (!$this->columnExists($db, 'content_blocks', 'created_by')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN created_by INT UNSIGNED NULL AFTER cache_ttl");
        }

        // Add version column
        if (!$this->columnExists($db, 'content_blocks', 'version')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN version INT UNSIGNED DEFAULT 1 AFTER created_by");
        }

        // Add position column for ordering
        if (!$this->columnExists($db, 'content_blocks', 'position')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN position INT UNSIGNED DEFAULT 0 AFTER version");
        }

        // Add updated_at column if not exists
        if (!$this->columnExists($db, 'content_blocks', 'updated_at')) {
            $db->exec("ALTER TABLE content_blocks ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }

        // Ensure updated_at updates automatically if it exists but doesn't have ON UPDATE
        // This is a safe no-op if already configured correctly

        // Add indexes for better performance
        if (!$this->indexExists($db, 'content_blocks', 'idx_category')) {
            $db->exec("CREATE INDEX idx_category ON content_blocks(category)");
        }

        if (!$this->indexExists($db, 'content_blocks', 'idx_is_active')) {
            $db->exec("CREATE INDEX idx_is_active ON content_blocks(is_active)");
        }

        if (!$this->indexExists($db, 'content_blocks', 'idx_type')) {
            $db->exec("CREATE INDEX idx_type ON content_blocks(type)");
        }

        return true;
    }

    private function columnExists(PDO $db, string $table, string $column): bool
    {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        return (bool)$stmt->fetchColumn();
    }

    private function indexExists(PDO $db, string $table, string $indexName): bool
    {
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $indexName]);
        return (bool)$stmt->fetchColumn();
    }
}
