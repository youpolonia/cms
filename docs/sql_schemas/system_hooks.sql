CREATE TABLE system_hooks (
    id VARCHAR(36) PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
CREATE INDEX idx_system_hooks_name ON system_hooks (name);