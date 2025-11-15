-- Version Changes Table
-- Tracks detailed changes between content versions
-- Created: 2025-05-11
-- Author: Roo (DB Support)

CREATE TABLE IF NOT EXISTS version_changes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    from_version_id BIGINT UNSIGNED NOT NULL COMMENT 'Source version ID',
    to_version_id BIGINT UNSIGNED NOT NULL COMMENT 'Target version ID',
    change_type ENUM('content', 'metadata', 'both') NOT NULL COMMENT 'Type of change',
    content_diff MEDIUMTEXT COMMENT 'Text diff between versions',
    changed_fields JSON COMMENT 'JSON array of changed field names',
    change_size INT UNSIGNED COMMENT 'Approximate change size in bytes',
    automated BOOLEAN DEFAULT FALSE COMMENT 'Flag for automated changes',
    
    FOREIGN KEY (from_version_id) 
        REFERENCES content_versions(id)
        ON DELETE CASCADE,
    FOREIGN KEY (to_version_id) 
        REFERENCES content_versions(id)
        ON DELETE CASCADE,
        
    INDEX idx_from_version (from_version_id),
    INDEX idx_to_version (to_version_id),
    INDEX idx_change_type (change_type),
    INDEX idx_automated (automated),
    UNIQUE INDEX uniq_version_pair (from_version_id, to_version_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tracks changes between content versions';

-- Sample data for testing (commented out in production)
-- INSERT INTO version_changes
-- (from_version_id, to_version_id, change_type, content_diff, changed_fields, change_size)
-- VALUES
-- (1, 2, 'content', '@@ -1 +1 @@\n-Sample content\n+Updated content', '["content"]', 7);