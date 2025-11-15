# Content Types Module Database Schema

## Tables Structure

### content_types
```sql
CREATE TABLE content_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    settings JSON,
    tenant_id VARCHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tenant (tenant_id),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### content_fields
```sql
CREATE TABLE content_fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    content_type_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    type ENUM('text','textarea','number','date','datetime','boolean','select','json') NOT NULL,
    settings JSON,
    is_required BOOLEAN DEFAULT FALSE,
    sort_order INT UNSIGNED DEFAULT 0,
    tenant_id VARCHAR(36) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_content_type (content_type_id),
    INDEX idx_tenant (tenant_id),
    INDEX idx_sort (sort_order),
    FOREIGN KEY (content_type_id) REFERENCES content_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## Relationships
- One content_type has many content_fields (1:N)
- Both tables reference tenant_id for isolation

## Sample Data

### content_types
```sql
INSERT INTO content_types (name, description, settings, tenant_id) VALUES
('Blog Post', 'Standard blog post content type', '{"allow_comments":true,"default_status":"draft"}', 'tenant1'),
('Product', 'E-commerce product', '{"inventory_tracking":true,"taxable":true}', 'tenant1');
```

### content_fields
```sql
INSERT INTO content_fields (content_type_id, name, type, settings, is_required, sort_order, tenant_id) VALUES
(1, 'Title', 'text', '{"max_length":255}', TRUE, 1, 'tenant1'),
(1, 'Body', 'textarea', '{"rich_text":true}', TRUE, 2, 'tenant1'),
(1, 'Published At', 'datetime', '{"future_only":false}', FALSE, 3, 'tenant1'),
(2, 'Name', 'text', '{"max_length":100}', TRUE, 1, 'tenant1'),
(2, 'Price', 'number', '{"min":0,"precision":2}', TRUE, 2, 'tenant1'),
(2, 'Description', 'textarea', '{"rich_text":false}', FALSE, 3, 'tenant1');
```

## Implementation Notes
1. Uses JSON columns for flexible settings storage
2. Maintains tenant isolation via tenant_id
3. Includes standard timestamps for auditing
4. Uses foreign key for data integrity
5. Optimized indexes for common queries