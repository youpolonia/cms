-- Content Types Table
CREATE TABLE IF NOT EXISTS content_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    machine_name VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Content Fields Table
CREATE TABLE IF NOT EXISTS content_fields (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_type_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    machine_name VARCHAR(255) NOT NULL,
    field_type VARCHAR(255) NOT NULL,
    settings JSON,
    is_required BOOLEAN DEFAULT FALSE,
    weight INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (content_type_id) REFERENCES content_types(id) ON DELETE CASCADE,
    UNIQUE KEY unique_field_per_type (content_type_id, machine_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;