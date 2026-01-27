-- JTB Theme Builder - Database Migration
-- Run this SQL to add Theme Builder tables

-- Modify jtb_templates table
ALTER TABLE jtb_templates
MODIFY COLUMN type ENUM('header', 'footer', 'body') NOT NULL,
ADD COLUMN css_cache TEXT AFTER content,
ADD COLUMN is_default TINYINT(1) DEFAULT 0 AFTER css_cache,
ADD COLUMN priority INT DEFAULT 10 AFTER is_default,
DROP COLUMN conditions,
ADD INDEX idx_default (is_default),
ADD INDEX idx_priority (priority);

-- Create jtb_template_conditions table
CREATE TABLE IF NOT EXISTS jtb_template_conditions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    condition_type ENUM('include', 'exclude') NOT NULL DEFAULT 'include',
    page_type VARCHAR(50) NOT NULL,
    object_id INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_template (template_id),
    INDEX idx_page_type (page_type),
    FOREIGN KEY (template_id) REFERENCES jtb_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to jtb_global_modules if not exist
ALTER TABLE jtb_global_modules
ADD COLUMN IF NOT EXISTS updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS description TEXT,
ADD COLUMN IF NOT EXISTS thumbnail VARCHAR(255);
