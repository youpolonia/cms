<?php
declare(strict_types=1);

/**
 * Migration: Upgrade menus and menu_items tables
 * Adds: is_active, max_depth, icon, description, visibility, timestamps
 */

return new class {
    public function up(\PDO $pdo): void
    {
        // === MENUS TABLE UPGRADES ===
        
        if (!$this->columnExists($pdo, 'menus', 'is_active')) {
            $pdo->exec("ALTER TABLE menus ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        }
        
        if (!$this->columnExists($pdo, 'menus', 'max_depth')) {
            $pdo->exec("ALTER TABLE menus ADD COLUMN max_depth TINYINT DEFAULT 3");
        }
        
        if (!$this->columnExists($pdo, 'menus', 'created_by')) {
            $pdo->exec("ALTER TABLE menus ADD COLUMN created_by INT UNSIGNED NULL");
        }
        
        if (!$this->columnExists($pdo, 'menus', 'updated_at')) {
            $pdo->exec("ALTER TABLE menus ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }
        
        // === MENU_ITEMS TABLE UPGRADES ===
        
        if (!$this->columnExists($pdo, 'menu_items', 'icon')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN icon VARCHAR(50) NULL");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'description')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN description VARCHAR(255) NULL");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'is_active')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN is_active TINYINT(1) DEFAULT 1");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'visibility')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN visibility ENUM('all','logged_in','logged_out','admin') DEFAULT 'all'");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'open_in_new_tab')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN open_in_new_tab TINYINT(1) DEFAULT 0");
            // Migrate existing target values
            $pdo->exec("UPDATE menu_items SET open_in_new_tab = 1 WHERE target = '_blank'");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'created_at')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
        
        if (!$this->columnExists($pdo, 'menu_items', 'updated_at')) {
            $pdo->exec("ALTER TABLE menu_items ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
        }
    }

    public function down(\PDO $pdo): void
    {
        // Menus - MySQL 8.0+ syntax
        $columns = ['is_active', 'max_depth', 'created_by', 'updated_at'];
        foreach ($columns as $col) {
            if ($this->columnExists($pdo, 'menus', $col)) {
                $pdo->exec("ALTER TABLE menus DROP COLUMN {$col}");
            }
        }
        
        // Menu items
        $columns = ['icon', 'description', 'is_active', 'visibility', 'open_in_new_tab', 'created_at', 'updated_at'];
        foreach ($columns as $col) {
            if ($this->columnExists($pdo, 'menu_items', $col)) {
                $pdo->exec("ALTER TABLE menu_items DROP COLUMN {$col}");
            }
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
