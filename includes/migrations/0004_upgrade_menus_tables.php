<?php
/**
 * Migration: Upgrade menus and menu_items tables
 * Adds: is_active, max_depth, icon, description, visibility, timestamps
 */

require_once __DIR__ . '/abstractmigration.php';

class Migration_0004_upgrade_menus_tables extends AbstractMigration
{
    public function execute(PDO $db): bool
    {
        // === MENUS TABLE UPGRADES ===
        
        // Add is_active column
        if (!$this->columnExists($db, 'menus', 'is_active')) {
            $db->exec("ALTER TABLE menus ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER location");
        }
        
        // Add max_depth column
        if (!$this->columnExists($db, 'menus', 'max_depth')) {
            $db->exec("ALTER TABLE menus ADD COLUMN max_depth TINYINT DEFAULT 3 AFTER is_active");
        }
        
        // Add created_by column
        if (!$this->columnExists($db, 'menus', 'created_by')) {
            $db->exec("ALTER TABLE menus ADD COLUMN created_by INT UNSIGNED NULL AFTER max_depth");
        }
        
        // Add updated_at column
        if (!$this->columnExists($db, 'menus', 'updated_at')) {
            $db->exec("ALTER TABLE menus ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        }
        
        // === MENU_ITEMS TABLE UPGRADES ===
        
        // Add icon column
        if (!$this->columnExists($db, 'menu_items', 'icon')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN icon VARCHAR(50) NULL AFTER title");
        }
        
        // Add description column
        if (!$this->columnExists($db, 'menu_items', 'description')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN description VARCHAR(255) NULL AFTER icon");
        }
        
        // Add is_active column
        if (!$this->columnExists($db, 'menu_items', 'is_active')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER css_class");
        }
        
        // Add visibility column
        if (!$this->columnExists($db, 'menu_items', 'visibility')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN visibility ENUM('all','logged_in','logged_out','admin') DEFAULT 'all' AFTER is_active");
        }
        
        // Add open_in_new_tab column (replaces target)
        if (!$this->columnExists($db, 'menu_items', 'open_in_new_tab')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN open_in_new_tab TINYINT(1) DEFAULT 0 AFTER visibility");
            // Migrate existing target values
            $db->exec("UPDATE menu_items SET open_in_new_tab = 1 WHERE target = '_blank'");
        }
        
        // Add created_at column
        if (!$this->columnExists($db, 'menu_items', 'created_at')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
        }
        
        // Add updated_at column
        if (!$this->columnExists($db, 'menu_items', 'updated_at')) {
            $db->exec("ALTER TABLE menu_items ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
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
}
