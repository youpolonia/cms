<?php
declare(strict_types=1);

/**
 * Migration: Upgrade Content Blocks Table
 * Adds: category, cache_ttl, created_by, version, position
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // Add category column
        if (!$this->columnExists($pdo, 'content_blocks', 'category')) {
            $pdo->exec("ALTER TABLE content_blocks ADD COLUMN category VARCHAR(50) DEFAULT 'global' AFTER type");
        }
        
        // Add cache_ttl column (0 = no cache)
        if (!$this->columnExists($pdo, 'content_blocks', 'cache_ttl')) {
            $pdo->exec("ALTER TABLE content_blocks ADD COLUMN cache_ttl INT UNSIGNED DEFAULT 0 AFTER category");
        }
        
        // Add created_by column
        if (!$this->columnExists($pdo, 'content_blocks', 'created_by')) {
            $pdo->exec("ALTER TABLE content_blocks ADD COLUMN created_by INT UNSIGNED NULL AFTER is_active");
        }
        
        // Add version column for versioning
        if (!$this->columnExists($pdo, 'content_blocks', 'version')) {
            $pdo->exec("ALTER TABLE content_blocks ADD COLUMN version INT UNSIGNED DEFAULT 1 AFTER created_by");
        }
        
        // Add position column for ordering
        if (!$this->columnExists($pdo, 'content_blocks', 'position')) {
            $pdo->exec("ALTER TABLE content_blocks ADD COLUMN position INT UNSIGNED DEFAULT 0 AFTER version");
        }
        
        // Add index for category (MySQL compatible)
        if (!$this->indexExists($pdo, 'content_blocks', 'idx_content_blocks_category')) {
            $pdo->exec("CREATE INDEX idx_content_blocks_category ON content_blocks(category)");
        }
        
        // Add index for is_active
        if (!$this->indexExists($pdo, 'content_blocks', 'idx_content_blocks_active')) {
            $pdo->exec("CREATE INDEX idx_content_blocks_active ON content_blocks(is_active)");
        }
    }

    public function down(\PDO $pdo): void
    {
        $columns = ['category', 'cache_ttl', 'created_by', 'version', 'position'];
        foreach ($columns as $col) {
            if ($this->columnExists($pdo, 'content_blocks', $col)) {
                $pdo->exec("ALTER TABLE content_blocks DROP COLUMN {$col}");
            }
        }
        
        // Drop indexes (ignore errors if don't exist)
        try {
            $pdo->exec("DROP INDEX idx_content_blocks_category ON content_blocks");
            $pdo->exec("DROP INDEX idx_content_blocks_active ON content_blocks");
        } catch (\Exception $e) {
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
    
    private function indexExists(\PDO $pdo, string $table, string $indexName): bool
    {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM information_schema.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
              AND TABLE_NAME = ? 
              AND INDEX_NAME = ?
        ");
        $stmt->execute([$table, $indexName]);
        return (bool)$stmt->fetchColumn();
    }
};