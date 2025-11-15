# Database Schema Documentation

## Core Tables

### `versions` (Content Version Control)
- `id`: BIGINT (PK, auto-increment)
- `content_id`: BIGINT (FK to contents.id)
- `version_number`: VARCHAR(20) (SemVer format)
- `created_at`: DATETIME
- `created_by`: BIGINT (FK to users.id)
- `status`: ENUM('draft','pending_approval','approved','published','archived')
- `is_current`: BOOLEAN (indicates live version)

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `version_identifier` (`content_id`, `version_number`)
- INDEX `status_idx` (`status`)
- INDEX `current_version_idx` (`content_id`, `is_current`)

**Constraints:**
- FOREIGN KEY (`content_id`) REFERENCES `contents`(`id`) ON DELETE CASCADE
- FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL

Relationships:
- Has many `version_content` (1:N)
- Has many `version_metadata` (1:N)
- Belongs to `contents` (N:1)

### `version_content` (Version Content Storage)
- `id`: BIGINT (PK)
- `version_id`: BIGINT (FK to versions.id)
- `content_type`: ENUM('html','markdown','json')
- `content_hash`: VARCHAR(64) (SHA-256)
- `content`: LONGTEXT
- `compressed`: BOOLEAN
- `compression_type`: ENUM('gzip','zstd')

**Indexes:**
- PRIMARY KEY (`id`)
- UNIQUE KEY `version_content_idx` (`version_id`, `content_type`)
- INDEX `content_hash_idx` (`content_hash`)

**Constraints:**
- FOREIGN KEY (`version_id`) REFERENCES `versions`(`id`) ON DELETE CASCADE

### `version_metadata` (Version Change Tracking)
- `id`: BIGINT (PK)
- `version_id`: BIGINT (FK to versions.id)  
- `change_type`: ENUM('create','update','revert')
- `change_description`: TEXT
- `changed_fields`: JSON (array of field names)
- `previous_values`: JSON
- `diff_summary`: TEXT

### `workflows` (Workflow Definitions)
- `id`: BIGINT (PK)
- `name`: VARCHAR(100)
- `description`: TEXT  
- `content_type`: VARCHAR(50) (matches content_types.name)
- `is_active`: BOOLEAN
- `version`: INT
- `created_at`: DATETIME
- `created_by`: BIGINT (FK to users.id)

### `status_transitions` (Allowed Status Changes)
- `id`: BIGINT (PK)
- `workflow_id`: BIGINT (FK to workflows.id)
- `from_status`: VARCHAR(50)
- `to_status`: VARCHAR(50)  
- `permission_required`: VARCHAR(100)
- `auto_approve`: BOOLEAN
- `notification_template`: VARCHAR(100)

### `analytics_monthly_summary`
- `id`: BIGINT (PK)
- `content_id`: BIGINT (FK to contents.id)
- `year_month`: CHAR(7) (YYYY-MM)
- `view_count`: INT
- `like_count`: INT  
- `comment_count`: INT
- `unique_visitors`: INT
- `avg_time_on_page`: FLOAT

### `workflow_version_snapshots`
- `id`: BIGINT (PK)
- `workflow_id`: BIGINT (FK to workflows.id)
- `version`: INT  
- `snapshot_data`: JSON
- `created_at`: DATETIME
- `created_by`: BIGINT (FK to users.id)

### `version_analytics`
- `id`: BIGINT (PK)
- `version_id`: BIGINT (FK to versions.id)
- `event_type`: VARCHAR(50)  
- `event_data`: JSON
- `created_at`: DATETIME
- `user_id`: BIGINT (FK to users.id, nullable)
- `ip_address`: VARCHAR(45)

## Multi-Tenant Tables
- `tenants`: Tenant information
- `content_pages`: Tenant-specific content
- `content_blocks`: Tenant-specific content blocks
- `tenant_analytics_events`: Tenant analytics data
- `user_sites`: User-to-tenant mappings

## System Tables
- `system_scheduler_log`: Scheduler execution history  
- `system_alerts`: System alerts and notifications
- `system_tasks`: Background tasks
- `workers`: Worker processes
- `worker_metrics`: Worker performance metrics
- `rate_limits`: API rate limiting

## Content Management
- `content_types`: Content type definitions
- `contents`: Main content storage
- `content_flags`: Flagged content
- `content_schedules`: Scheduled publishing
- `content_workflow`: Workflow state tracking
- `content_workflow_history`: Workflow transition history

## AI Integration
- `ai_provider_configs`: AI service configurations
- `seo_analysis`: SEO recommendations
- `media_metadata`: AI-generated media tags

## Theme Management  
- `theme_variables`: Theme customization variables
- `settings`: System settings (including themes)

## Full list of 58 tables identified (complete documentation in progress)