-- Content Versions Table
-- Stores all versions of CMS content with basic metadata
-- Created: 2025-05-11
-- Author: Roo (DB Support)

CREATE TABLE IF NOT EXISTS content_versions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    content_id BIGINT UNSIGNED NOT NULL COMMENT 'Reference to original content',
    version_number INT UNSIGNED NOT NULL COMMENT 'Sequential version number',
    title VARCHAR(255) NOT NULL COMMENT 'Version title/summary',
    content LONGTEXT NOT NULL COMMENT 'Actual content data',
    content_hash CHAR(64) NOT NULL COMMENT 'SHA-256 hash of content',
    is_autosave BOOLEAN DEFAULT FALSE COMMENT 'Flag for autosave versions',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Version creation time',
    created_by BIGINT UNSIGNED NOT NULL COMMENT 'User ID who created version',
    
    INDEX idx_content_id (content_id),
    INDEX idx_version_number (version_number),
    INDEX idx_created_at (created_at),
    INDEX idx_created_by (created_by),
    UNIQUE INDEX uniq_content_version (content_id, version_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores all versions of CMS content';

-- Sample data for testing (commented out in production)
-- INSERT INTO content_versions 
-- (content_id, version_number, title, content, content_hash, created_by)
-- VALUES
-- (1, 1, 'Initial version', 'Sample content', SHA2('Sample content', 256), 1),
-- (1, 2, 'Updated version', 'Updated content', SHA2('Updated content', 256), 1);