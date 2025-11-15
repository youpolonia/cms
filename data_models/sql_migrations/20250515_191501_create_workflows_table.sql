-- Migration: 20250515_191501_create_workflows_table
-- UP Migration: Creates workflows table with role foreign key
CREATE TABLE IF NOT EXISTS workflows (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    steps JSON NOT NULL COMMENT 'JSON array of workflow steps with permissions',
    content_types JSON NULL COMMENT 'Content types this workflow applies to',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DOWN Migration: Drops workflows table
DROP TABLE IF EXISTS workflows;