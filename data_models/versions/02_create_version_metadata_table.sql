-- Version Metadata Table
-- Stores extended metadata about content versions
-- Created: 2025-05-11
-- Author: Roo (DB Support)

CREATE TABLE IF NOT EXISTS version_metadata (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    version_id BIGINT UNSIGNED NOT NULL COMMENT 'Reference to content_versions.id',
    change_reason VARCHAR(255) COMMENT 'Reason for version creation',
    editor_notes TEXT COMMENT 'Editor comments about changes',
    is_major_version BOOLEAN DEFAULT FALSE COMMENT 'Flag for significant versions',
    custom_data JSON COMMENT 'Additional custom metadata',
    
    FOREIGN KEY (version_id) 
        REFERENCES content_versions(id)
        ON DELETE CASCADE,
        
    INDEX idx_version_id (version_id),
    INDEX idx_is_major_version (is_major_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Extended metadata for content versions';

-- Sample data for testing (commented out in production)
-- INSERT INTO version_metadata
-- (version_id, change_reason, editor_notes, is_major_version)
-- VALUES
-- (1, 'Initial creation', 'First version of content', TRUE),
-- (2, 'Content update', 'Fixed typos and improved clarity', FALSE);