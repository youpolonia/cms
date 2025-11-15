# Database Schema Documentation

This document details the schema of the CMS database, based on the provided migration files.

## Core Authentication & Authorization

### `users`
- **Primary Key:** `id`
- **Description:** Stores general user accounts for the CMS.
- **Notes:** `site_id` is `INT UNSIGNED` but `sites.id` is `BIGINT UNSIGNED`.

| Column                   | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|--------------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`                     | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`                | `INT UNSIGNED`              | Yes      | `NULL`            | `INDEX`                                              | `sites(id)` (Type Mismatch) |
| `username`               | `VARCHAR(50)`               | No       |                   | `UNIQUE`                                             |                            |
| `email`                  | `VARCHAR(100)`              | No       |                   | `UNIQUE`                                             |                            |
| `password`               | `VARCHAR(255)`              | No       |                   |                                                      |                            |
| `first_name`             | `VARCHAR(50)`               | Yes      | `NULL`            |                                                      |                            |
| `last_name`              | `VARCHAR(50)`               | Yes      | `NULL`            |                                                      |                            |
| `is_active`              | `BOOLEAN`                   | No       | `1`               |                                                      |                            |
| `last_login`             | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `created_at`             | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`             | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |
| `password_reset_token`   | `VARCHAR(60)`               | Yes      | `NULL`            | `UNIQUE`                                             |                            |
| `password_reset_expires` | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `email_verified_at`      | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |

### `roles`
- **Primary Key:** `id`
- **Description:** Defines roles within the CMS.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `name`        | `VARCHAR(50)`               | No       |                   | `UNIQUE`                                             |                            |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `permissions`
- **Primary Key:** `id`
- **Description:** Defines specific permissions.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `name`        | `VARCHAR(100)`              | No       |                   | `UNIQUE`                                             |                            |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `role_permissions`
- **Primary Key:** (`role_id`, `permission_id`)
- **Description:** Pivot table linking roles to permissions.

| Column          | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To                |
|-----------------|------------|----------|-------------------|---------------------|-------------------------------|
| `role_id`       | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `roles(id)` (CASCADE)         |
| `permission_id` | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `permissions(id)` (CASCADE)   |
| `created_at`    | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                               |

### `user_roles`
- **Primary Key:** (`user_id`, `role_id`)
- **Description:** Pivot table linking users to roles.

| Column       | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To          |
|--------------|------------|----------|-------------------|---------------------|-------------------------|
| `user_id`    | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `users(id)` (CASCADE)   |
| `role_id`    | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `roles(id)` (CASCADE)   |
| `created_at` | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                         |

### `remember_tokens`
- **Primary Key:** `id`
- **Description:** Stores "remember me" tokens for user sessions.

| Column       | Type                        | Nullable | Default           | Constraints/Indexes | Foreign Key To          |
|--------------|-----------------------------|----------|-------------------|---------------------|-------------------------|
| `id`         | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`       |                         |
| `user_id`    | `INT`                       | No       |                   | `INDEX`, `FK`       | `users(id)` (CASCADE)   |
| `token`      | `VARCHAR(255)`              | No       |                   | `UNIQUE`            |                         |
| `expires_at` | `DATETIME`                  | No       |                   |                     |                         |
| `created_at` | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                     |                         |

## Content Management

### `content_types`
- **Primary Key:** `id`
- **Description:** Defines different types of content (e.g., page, post).
- **Notes:** This definition is from `phase1/0006_create_core_content_tables.php`. Another simpler definition exists in `phase2/0002_create_content_types_table.php` which might be redundant or for a different purpose.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|--------------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`             | `VARCHAR(50)`               | No       |                   | `UNIQUE`                                             |
| `slug`             | `VARCHAR(50)`               | No       |                   | `UNIQUE`                                             |
| `description`      | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `is_hierarchical`  | `TINYINT(1)`                | No       | `0`               |                                                      |
| `has_tags`         | `TINYINT(1)`                | No       | `1`               |                                                      |
| `has_categories`   | `TINYINT(1)`                | No       | `1`               |                                                      |
| `created_at`       | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`       | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `content_items`
- **Primary Key:** `id`
- **Description:** Stores individual content entries. Referred to as `contents` in some older FKs.

| Column             | Type                                              | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|--------------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`               | `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |                                      |
| `site_id`          | `BIGINT UNSIGNED`                                 | No       |                   | `INDEX`, `FK`                                        | `sites(id)` (CASCADE)                |
| `content_type_id`  | `INT`                                             | No       |                   | `FK`                                                 | `content_types(id)` (CASCADE)        |
| `author_id`        | `INT`                                             | No       |                   | `INDEX`, `FK`                                        | `users(id)` (RESTRICT)               |
| `parent_id`        | `INT`                                             | Yes      | `NULL`            | `FK`                                                 | `content_items(id)` (CASCADE)        |
| `title`            | `VARCHAR(255)`                                    | No       |                   |                                                      |                                      |
| `slug`             | `VARCHAR(255)`                                    | No       |                   | `INDEX (slug, content_type_id)`                      |                                      |
| `content_body`     | `LONGTEXT`                                        | Yes      | `NULL`            |                                                      |                                      |
| `excerpt`          | `TEXT`                                            | Yes      | `NULL`            |                                                      |                                      |
| `status`           | `ENUM('draft', 'scheduled', 'published', 'archived', 'expired')` | No       | `draft`           | `INDEX`                                              |                                      |
| `visibility`       | `VARCHAR(20)`                                     | No       | `public`          |                                                      |                                      |
| `password`         | `VARCHAR(255)`                                    | Yes      | `NULL`            |                                                      |                                      |
| `published_at`     | `TIMESTAMP`                                       | Yes      | `NULL`            | `INDEX`                                              |                                      |
| `archived_at`      | `TIMESTAMP`                                       | Yes      | `NULL`            | `INDEX`                                              |                                      |
| `expired_at`       | `TIMESTAMP`                                       | Yes      | `NULL`            | `INDEX`                                              |                                      |
| `created_at`       | `DATETIME`                                        | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `updated_at`       | `DATETIME`                                        | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                      |
| `publish_date`     | `DATE`                                            | Yes      | `NULL`            | (Added by `2025_05_12_154300_add_scheduling_fields.php`) |                                      |
| `lifecycle_status` | `VARCHAR(50)`                                     | Yes      | `NULL`            | (Added by `2025_05_12_154300_add_scheduling_fields.php`) |                                      |

### `categories`
- **Primary Key:** `id`
- **Description:** Content categories.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `name`        | `VARCHAR(100)`              | No       |                   |                                                      |                            |
| `slug`        | `VARCHAR(100)`              | No       |                   | `UNIQUE`                                             |                            |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `parent_id`   | `INT`                       | Yes      | `NULL`            | `FK`                                                 | `categories(id)` (SET NULL)|
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `tags`
- **Primary Key:** `id`
- **Description:** Content tags.

| Column       | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|--------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`         | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`       | `VARCHAR(100)`              | No       |                   |                                                      |
| `slug`       | `VARCHAR(100)`              | No       |                   | `UNIQUE`                                             |
| `created_at` | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at` | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `content_item_categories`
- **Primary Key:** (`content_item_id`, `category_id`)
- **Description:** Pivot table linking content items to categories.

| Column            | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To                   |
|-------------------|------------|----------|-------------------|---------------------|----------------------------------|
| `content_item_id` | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `content_items(id)` (CASCADE)    |
| `category_id`     | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `categories(id)` (CASCADE)       |
| `created_at`      | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                                  |

### `content_item_tags`
- **Primary Key:** (`content_item_id`, `tag_id`)
- **Description:** Pivot table linking content items to tags.

| Column            | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To                   |
|-------------------|------------|----------|-------------------|---------------------|----------------------------------|
| `content_item_id` | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `content_items(id)` (CASCADE)    |
| `tag_id`          | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `tags(id)` (CASCADE)             |
| `created_at`      | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                                  |

### `custom_fields`
- **Primary Key:** `id`
- **Description:** Defines custom fields for content types.

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                   |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------------|
| `id`              | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                  |
| `content_type_id` | `INT`                       | No       |                   | `FK`, `UNIQUE (content_type_id, field_name)`         | `content_types(id)` (CASCADE)    |
| `field_name`      | `VARCHAR(100)`              | No       |                   | `UNIQUE (content_type_id, field_name)`               |                                  |
| `field_label`     | `VARCHAR(255)`              | No       |                   |                                                      |                                  |
| `field_type`      | `VARCHAR(50)`               | No       |                   |                                                      |                                  |
| `options`         | `TEXT`                      | Yes      | `NULL`            |                                                      |                                  |
| `is_required`     | `TINYINT(1)`                | No       | `0`               |                                                      |                                  |
| `created_at`      | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                  |
| `updated_at`      | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                  |

### `custom_field_values`
- **Primary Key:** `id`
- **Description:** Stores values for custom fields on content items.

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                   |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------------|
| `id`              | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                  |
| `content_item_id` | `INT`                       | No       |                   | `FK`, `INDEX (content_item_id, custom_field_id)`     | `content_items(id)` (CASCADE)    |
| `custom_field_id` | `INT`                       | No       |                   | `FK`, `INDEX (content_item_id, custom_field_id)`     | `custom_fields(id)` (CASCADE)    |
| `field_value`     | `LONGTEXT`                  | Yes      | `NULL`            |                                                      |                                  |
| `created_at`      | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                  |
| `updated_at`      | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                  |

## Version Control

### `versions`
- **Primary Key:** `id`
- **Description:** Stores versions of content items. Referred to as `content_versions` in some older SQL scripts.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|--------------------|-----------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                      |
| `content_id`       | `INT`                       | No       |                   | `INDEX (content_id, version_number)`                 | `content_items(id)` (CASCADE)        |
| `user_id`          | `INT`                       | Yes      | `NULL`            | `FK`                                                 | `users(id)` (SET NULL)               |
| `version_number`   | `INT`                       | No       |                   | `INDEX (content_id, version_number)`                 |                                      |
| `created_at`       | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |                                      |
| `notes`            | `TEXT`                      | Yes      | `NULL`            |                                                      |                                      |
| `is_autosave`      | `BOOLEAN`                   | No       | `0`               | `INDEX`                                              |                                      |
| `publish_date`     | `DATE`                      | Yes      | `NULL`            | (Added by `2025_05_12_154300_add_scheduling_fields.php`) |                                      |
| `lifecycle_status` | `VARCHAR(50)`               | Yes      | `NULL`            | (Added by `2025_05_12_154300_add_scheduling_fields.php`) |                                      |

### `version_metadata`
- **Primary Key:** `id`
- **Description:** Stores metadata associated with each version.

| Column                | Type                                                     | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------------|----------------------------------------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`                  | `INT AUTO_INCREMENT`                                     | No       |                   | `PRIMARY KEY`                                        |                            |
| `version_id`          | `INT`                                                    | No       |                   | `UNIQUE`, `FK`                                       | `versions(id)` (CASCADE)   |
| `meta_key`            | `VARCHAR(255)`                                           | No       |                   | `INDEX`                                              |                            |
| `meta_value`          | `TEXT`                                                   | Yes      | `NULL`            |                                                      |                            |
| `change_type`         | `VARCHAR(50)`                                            | Yes      | `NULL`            | `INDEX`                                              |                            |
| `previous_version_id` | `INT`                                                    | Yes      | `NULL`            | `INDEX`, `FK`                                        | `versions(id)` (SET NULL)  |
| `tags`                | `JSON`                                                   | Yes      | `NULL`            |                                                      |                            |
| `seo_title`           | `VARCHAR(255)`                                           | Yes      | `NULL`            | (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `seo_description`     | `TEXT`                                                   | Yes      | `NULL`            | (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `seo_keywords`        | `VARCHAR(255)`                                           | Yes      | `NULL`            | (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `author_id`           | `INT`                                                    | Yes      | `NULL`            | `FK` (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) | `users(id)`                |
| `published_at`        | `TIMESTAMP`                                              | Yes      | `NULL`            | `INDEX` (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `status`              | `ENUM('draft', 'pending_review', 'published', 'archived')` | Yes      | `draft`           | `INDEX` (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `visibility`          | `ENUM('public', 'private', 'password_protected')`        | Yes      | `public`          | `INDEX` (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `created_at`          | `TIMESTAMP`                                              | Yes      | `CURRENT_TIMESTAMP` | (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |
| `updated_at`          | `TIMESTAMP`                                              | Yes      | `CURRENT_TIMESTAMP` (on update) | (Added by `2025_05_17_001200_enhance_version_metadata_for_analytics.php`) |                            |

### `version_content`
- **Primary Key:** `id`
- **Description:** Stores the actual content for each version.

| Column                | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`                  | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `version_id`          | `INT`                       | No       |                   | `UNIQUE`, `FK`                                       | `versions(id)` (CASCADE)   |
| `content`             | `MEDIUMTEXT`                | No       |                   |                                                      |                            |
| `content_hash`        | `VARCHAR(64)`               | No       |                   |                                                      |                            |
| `diff_content`        | `MEDIUMTEXT`                | Yes      | `NULL`            | (Added by `2025_05_17_001000_enhance_version_content_for_diffs.php`) |                            |
| `diff_format`         | `VARCHAR(20)`               | Yes      | `NULL`            | (Added by `2025_05_17_001000_enhance_version_content_for_diffs.php`) |                            |
| `previous_version_id` | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK` (Added by `2025_05_17_001000_enhance_version_content_for_diffs.php`) | `versions(id)`             |
| `is_full_content`     | `BOOLEAN`                   | Yes      | `1`               | (Added by `2025_05_17_001000_enhance_version_content_for_diffs.php`) |                            |

### `restoration_audit_log`
- **Primary Key:** `id`
- **Description:** Logs content restoration events.

| Column          | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`            | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`       | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `content_id`    | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `content_items(id)` (CASCADE) |
| `version_id`    | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `versions(id)` (CASCADE)   |
| `user_id`       | `INT`                       | No       |                   | `FK`                                                 | `users(id)` (RESTRICT)     |
| `restored_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `status`        | `VARCHAR(20)`               | No       |                   |                                                      |                            |
| `details`       | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `previous_data` | `TEXT`                      | Yes      | `NULL`            | (Added by `2025_05_12_013300_enhance_restoration_log.php`) |                            |

### `version_history`
- **Primary Key:** `id`
- **Description:** Generic history tracking for various entities.

| Column           | Type                               | Nullable | Default           | Constraints/Indexes                                  |
|------------------|------------------------------------|----------|-------------------|------------------------------------------------------|
| `id`             | `INT AUTO_INCREMENT`               | No       |                   | `PRIMARY KEY`                                        |
| `entity_type`    | `VARCHAR(100)`                     | No       |                   | `INDEX (entity_type, entity_id)`                     |
| `entity_id`      | `INT`                              | No       |                   | `INDEX (entity_type, entity_id)`                     |
| `version_number` | `INT`                              | No       |                   |                                                      |
| `user_id`        | `INT`                              | Yes      | `NULL`            | `INDEX` (Implied FK to `users(id)`)                  |
| `change_type`    | `ENUM('create', 'update', 'delete')` | No       |                   |                                                      |
| `old_value`      | `TEXT`                             | Yes      | `NULL`            |                                                      |
| `new_value`      | `TEXT`                             | Yes      | `NULL`            |                                                      |
| `change_reason`  | `TEXT`                             | Yes      | `NULL`            |                                                      |
| `created_at`     | `TIMESTAMP`                        | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |

### `conflict_resolution`
- **Primary Key:** `id`
- **Description:** Tracks resolution of conflicts between content versions.

| Column              | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`                | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `version_a_id`      | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `versions(id)`             |
| `version_b_id`      | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `versions(id)`             |
| `conflict_type`     | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `resolution_method` | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `resolved_by`       | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `users(id)`                |
| `resolved_at`       | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `notes`             | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |

## Scheduling & Workflows

### `scheduled_content`
- **Primary Key:** `id`
- **Description:** Manages scheduled publishing/unpublishing of content. Referred to as `scheduled_events` in some migrations.
- **Notes:** `site_id` added by `2025_05_15_002903_add_site_id_to_scheduled_content_table.php`. Recurrence fields added by `2025_05_18_000000_add_recurrence_fields.php`. Priority and worker assignment by `phase4/0001_add_priority_queue_features.php`.

| Column                  | Type                                              | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|-------------------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`                    | `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |                                      |
| `site_id`               | `BIGINT UNSIGNED`                                 | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)                |
| `content_id`            | `INT`                                             | No       |                   | `FK`                                                 | `content_items(id)` (CASCADE)        |
| `publish_time`          | `DATETIME`                                        | No       |                   | `INDEX`                                              |                                      |
| `expire_time`           | `DATETIME`                                        | Yes      | `NULL`            | `INDEX`                                              |                                      |
| `priority`              | `TINYINT`                                         | Yes      | `0`               | `INDEX` (Altered by `phase4/...`)                    |                                      |
| `status`                | `ENUM('scheduled', 'published', 'expired')`       | Yes      | `scheduled`       | `INDEX`                                              |                                      |
| `created_at`            | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `updated_at`            | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                      |
| `recurrence_pattern`    | `VARCHAR(20)`                                     | Yes      | `NULL`            |                                                      |                                      |
| `recurrence_end_date`   | `DATE`                                            | Yes      | `NULL`            |                                                      |                                      |
| `recurrence_interval`   | `INT`                                             | Yes      | `1`               |                                                      |                                      |
| `recurrence_byday`      | `VARCHAR(20)`                                     | Yes      | `NULL`            |                                                      |                                      |
| `recurrence_bymonthday` | `INT`                                             | Yes      | `NULL`            |                                                      |                                      |
| `parent_event_id`       | `INT`                                             | Yes      | `NULL`            | `FK`                                                 | `scheduled_content(id)` (CASCADE)    |
| `is_recurring`          | `TINYINT(1)`                                      | Yes      | `0`               |                                                      |                                      |
| `assigned_worker_id`    | `VARCHAR(255)`                                    | Yes      | `NULL`            | `INDEX`, `FK`                                        | `workers(worker_id)` (SET NULL)      |

### `workflows`
- **Primary Key:** `id`
- **Description:** Defines workflows.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT UNSIGNED AUTO_INCREMENT` | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `is_active`   | `BOOLEAN`                   | No       | `1`               |                                                      |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `workflow_events`
- **Primary Key:** `id`
- **Description:** Logs events related to workflow execution.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `workflow_id` | `INT UNSIGNED`              | No       |                   | `FK`                                                 | `workflows(id)` (CASCADE)  |
| `event_type`  | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `details`     | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `status`      | `VARCHAR(20)`               | No       |                   |                                                      |                            |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `workflow_triggers`
- **Primary Key:** `id`
- **Description:** Defines triggers that can start or advance workflows.

| Column           | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`             | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `workflow_id`    | `INT UNSIGNED`              | No       |                   | `FK`                                                 | `workflows(id)` (CASCADE)  |
| `trigger_type`   | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `trigger_config` | `JSON`                      | No       |                   |                                                      |                            |
| `created_at`     | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`     | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `workflow_actions`
- **Primary Key:** `id`
- **Description:** Defines actions to be performed as part of a workflow.

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`              | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `workflow_id`     | `INT UNSIGNED`              | No       |                   | `FK`                                                 | `workflows(id)` (CASCADE)  |
| `action_type`     | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `action_config`   | `JSON`                      | No       |                   |                                                      |                            |
| `execution_order` | `INT`                       | No       |                   |                                                      |                            |
| `created_at`      | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`      | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `workflow_states`
- **Primary Key:** `id`
- **Description:** Defines states within a content workflow.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `label`       | `VARCHAR(255)`              | No       |                   |                                                      |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `is_initial`  | `BOOLEAN`                   | No       | `0`               |                                                      |
| `is_terminal` | `BOOLEAN`                   | No       | `0`               |                                                      |
| `created_at`  | `TIMESTAMP`                 | Yes      | `NULL`            |                                                      |
| `updated_at`  | `TIMESTAMP`                 | Yes      | `NULL`            |                                                      |

### `content_workflow`
- **Primary Key:** `id`
- **Description:** Tracks the current workflow state of content items.
- **Notes:** FKs are commented out in migration and use `unsignedBigInteger` for columns referencing `INT` PKs (type mismatch).

| Column                | Type                        | Nullable | Default | Constraints/Indexes                                  | Foreign Key To (Intended)      |
|-----------------------|-----------------------------|----------|---------|------------------------------------------------------|--------------------------------|
| `id`                  | `INT AUTO_INCREMENT`        | No       |         | `PRIMARY KEY`                                        |                                |
| `content_id`          | `BIGINT UNSIGNED`           | No       |         | `UNIQUE`, `INDEX`                                    | `content_items(id)` (CASCADE)  |
| `workflow_state_id`   | `BIGINT UNSIGNED`           | No       |         | `INDEX`                                              | `workflow_states(id)` (RESTRICT)|
| `user_id`             | `BIGINT UNSIGNED`           | Yes      | `NULL`  | `INDEX`                                              | `users(id)` (SET NULL)         |
| `assigned_to_user_id` | `BIGINT UNSIGNED`           | Yes      | `NULL`  | `INDEX`                                              | `users(id)` (SET NULL)         |
| `notes`               | `TEXT`                      | Yes      | `NULL`  |                                                      |                                |
| `created_at`          | `TIMESTAMP`                 | Yes      | `NULL`  |                                                      |                                |
| `updated_at`          | `TIMESTAMP`                 | Yes      | `NULL`  |                                                      |                                |

### `content_workflow_history`
- **Primary Key:** `id`
- **Description:** Logs transitions of content items through workflow states.
- **Notes:** FKs are commented out in migration and use `unsignedBigInteger` for columns referencing `INT` PKs (type mismatch).

| Column                   | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To (Intended)      |
|--------------------------|-----------------------------|----------|-------------------|------------------------------------------------------|--------------------------------|
| `id`                     | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                |
| `content_id`             | `BIGINT UNSIGNED`           | No       |                   | `INDEX`                                              | `content_items(id)` (CASCADE)  |
| `from_workflow_state_id` | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `INDEX`                                              | `workflow_states(id)` (SET NULL)|
| `to_workflow_state_id`   | `BIGINT UNSIGNED`           | No       |                   | `INDEX`                                              | `workflow_states(id)` (RESTRICT)|
| `user_id`                | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `INDEX`                                              | `users(id)` (SET NULL)         |
| `notes`                  | `TEXT`                      | Yes      | `NULL`            |                                                      |                                |
| `transitioned_at`        | `TIMESTAMP`                 | No       | `CURRENT_TIMESTAMP` | `INDEX`                                              |                                |

### `workflow_monitoring`
- **Primary Key:** `id`
- **Description:** Tracks state changes for scheduled events/content.

| Column       | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                |
|--------------|-----------------------------|----------|-------------------|------------------------------------------------------|-------------------------------|
| `id`         | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                               |
| `event_id`   | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `scheduled_content(id)`       |
| `from_state` | `VARCHAR(50)`               | No       |                   |                                                      |                               |
| `to_state`   | `VARCHAR(50)`               | No       |                   |                                                      |                               |
| `changed_by` | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `users(id)`                   |
| `changed_at` | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                               |
| `notes`      | `TEXT`                      | Yes      | `NULL`            |                                                      |                               |

## Workers & Background Jobs

### `workers`
- **Primary Key:** `worker_id`
- **Description:** Defines background workers.

| Column               | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|----------------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `worker_id`          | `VARCHAR(255)`              | No       |                   | `PRIMARY KEY`                                        |
| `type`               | `VARCHAR(50)`               | No       |                   |                                                      |
| `status`             | `VARCHAR(20)`               | No       | `active`          |                                                      |
| `hostname`           | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |
| `pid`                | `INT`                       | Yes      | `NULL`            |                                                      |
| `last_heartbeat`     | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `created_at`         | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`         | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |
| `health_score`       | `TINYINT UNSIGNED`          | Yes      | `100`             | (Added by `2025_05_18_110800_add_worker_health_columns.php`) |
| `failure_count`      | `INT UNSIGNED`              | Yes      | `0`               | (Added by `2025_05_18_110800_add_worker_health_columns.php`) |
| `last_failure_time`  | `DATETIME`                  | Yes      | `NULL`            | (Added by `2025_05_18_110800_add_worker_health_columns.php`) |
| `recovery_attempts`  | `TINYINT UNSIGNED`          | Yes      | `0`               | (Added by `2025_05_18_110800_add_worker_health_columns.php`) |
| `current_workload`   | `INT`                       | No       | `0`               | `INDEX` (Added by `phase4/0001_add_priority_queue_features.php`) |
| `max_workload`       | `INT`                       | No       | `10`              | (Added by `phase4/0001_add_priority_queue_features.php`) |

### `human_workers`
- **Primary Key:** `id`
- **Description:** Profiles for human staff/workers, distinct from general `users`.

| Column                   | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|--------------------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`                     | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `username`               | `VARCHAR(50)`               | No       |                   | `UNIQUE`, `INDEX`                                    |
| `password`               | `VARCHAR(255)`              | No       |                   |                                                      |
| `email`                  | `VARCHAR(100)`              | No       |                   | `UNIQUE`, `INDEX`                                    |
| `first_name`             | `VARCHAR(50)`               | No       |                   |                                                      |
| `last_name`              | `VARCHAR(50)`               | No       |                   |                                                      |
| `phone`                  | `VARCHAR(20)`               | Yes      | `NULL`            |                                                      |
| `profile_picture`        | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |
| `role`                   | `VARCHAR(30)`               | No       | `worker`          | `INDEX`                                              |
| `last_password_change`   | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `password_reset_token`   | `VARCHAR(100)`              | Yes      | `NULL`            |                                                      |
| `password_reset_expires` | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `created_at`             | `DATETIME`                  | No       | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`             | `DATETIME`                  | No       | `CURRENT_TIMESTAMP` (on update) |                                      |

### `worker_roles`
- **Primary Key:** `id`
- **Description:** Roles specific to `human_workers` or the worker system.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `name`        | `VARCHAR(50)`               | No       |                   | `UNIQUE`                                             |                            |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `parent_id`   | `INT`                       | Yes      | `NULL`            | `FK`                                                 | `worker_roles(id)` (SET NULL)|
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `worker_permissions`
- **Primary Key:** `id`
- **Description:** Permissions specific to the worker system.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(100)`              | No       |                   | `UNIQUE`                                             |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `worker_role_permissions`
- **Primary Key:** (`role_id`, `permission_id`)
- **Description:** Links worker roles to worker permissions.

| Column          | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To                       |
|-----------------|------------|----------|-------------------|---------------------|--------------------------------------|
| `role_id`       | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `worker_roles(id)` (CASCADE)         |
| `permission_id` | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `worker_permissions(id)` (CASCADE)   |
| `created_at`    | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                                      |

### `worker_user_roles`
- **Primary Key:** (`user_id`, `role_id`)
- **Description:** Links users (likely `human_workers`) to worker roles.
- **Notes:** `user_id` FK target is ambiguous (could be `users.id` or `human_workers.id`).

| Column       | Type       | Nullable | Default           | Constraints/Indexes | Foreign Key To                |
|--------------|------------|----------|-------------------|---------------------|-------------------------------|
| `user_id`    | `INT`      | No       |                   | `PRIMARY KEY`       | (Ambiguous: `users` or `human_workers`) |
| `role_id`    | `INT`      | No       |                   | `PRIMARY KEY`, `FK` | `worker_roles(id)` (CASCADE)  |
| `created_at` | `DATETIME` | Yes      | `CURRENT_TIMESTAMP` |                     |                               |

### `worker_activity_logs`
- **Primary Key:** `log_id`
- **Description:** Logs activities performed by workers.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `log_id`      | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `worker_id`   | `VARCHAR(255)`              | No       |                   | `INDEX`, `FK`                                        | `workers(worker_id)` (CASCADE) |
| `action_type` | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `details`     | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `timestamp`   | `DATETIME`                  | No       | `CURRENT_TIMESTAMP` | `INDEX`                                              |                            |

### `heartbeat_alerts`
- **Primary Key:** `id`
- **Description:** Tracks heartbeat failures and alerts for workers.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes | Foreign Key To             |
|--------------------|-----------------------------|----------|-------------------|---------------------|----------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`       |                            |
| `worker_id`        | `VARCHAR(255)`              | No       |                   | `FK`                | `workers(worker_id)` (CASCADE) |
| `failure_count`    | `INT`                       | Yes      | `1`               |                     |                            |
| `last_failure`     | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                     |                            |
| `next_alert_level` | `INT`                       | Yes      | `1`               |                     |                            |
| `is_suppressed`    | `BOOLEAN`                   | Yes      | `0`               |                     |                            |

### `alert_configurations`
- **Primary Key:** `id`
- **Description:** Configures alert levels and notifications for heartbeat system.

| Column                     | Type                        | Nullable | Default | Constraints/Indexes |
|----------------------------|-----------------------------|----------|---------|---------------------|
| `id`                       | `INT AUTO_INCREMENT`        | No       |         | `PRIMARY KEY`       |
| `alert_level`              | `INT`                       | No       |         |                     |
| `threshold_minutes`        | `INT`                       | No       |         |                     |
| `notification_channels`  | `JSON`                      | No       |         |                     |
| `escalation_after_minutes` | `INT`                       | Yes      | `NULL`  |                     |
| `is_active`                | `BOOLEAN`                   | Yes      | `1`     |                     |

### `worker_notifications`
- **Primary Key:** `id`
- **Description:** Stores notifications for workers.

| Column         | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`           | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `worker_id`    | `VARCHAR(255)`              | No       |                   | `INDEX`, `FK`                                        | `workers(worker_id)` (CASCADE) |
| `message`      | `TEXT`                      | No       |                   |                                                      |                            |
| `type`         | `VARCHAR(50)`               | No       | `info`            |                                                      |                            |
| `read_at`      | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `batch_id`     | `VARCHAR(36)`               | Yes      | `NULL`            | `INDEX` (Added by `phase4_5_optimize_notifications.php`) |                            |
| `processed_at` | `DATETIME`                  | Yes      | `NULL`            | (Added by `phase4_5_optimize_notifications.php`) |                            |
| `priority`     | `TINYINT`                   | No       | `3`               | `INDEX` (Added by `phase4_5_optimize_notifications.php`) |                            |
| `created_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `notification_batches`
- **Primary Key:** `batch_id`
- **Description:** Tracks batches for processing notifications.

| Column               | Type                                              | Nullable | Default           | Constraints/Indexes                                  |
|----------------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|
| `batch_id`           | `VARCHAR(36)`                                     | No       |                   | `PRIMARY KEY`                                        |
| `status`             | `ENUM('pending', 'processing', 'completed', 'failed')` | No       | `pending`         | `INDEX`                                              |
| `created_at`         | `DATETIME`                                        | No       | `CURRENT_TIMESTAMP` | `INDEX`                                              |
| `started_at`         | `DATETIME`                                        | Yes      | `NULL`            |                                                      |
| `completed_at`       | `DATETIME`                                        | Yes      | `NULL`            |                                                      |
| `notification_count` | `INT`                                             | No       | `0`               |                                                      |

### `worker_metrics`
- **Primary Key:** `id`
- **Description:** Stores performance metrics for workers.

| Column           | Type                        | Nullable | Default | Constraints/Indexes                                  | Foreign Key To             |
|------------------|-----------------------------|----------|---------|------------------------------------------------------|----------------------------|
| `id`             | `INT AUTO_INCREMENT`        | No       |         | `PRIMARY KEY`                                        |                            |
| `worker_id`      | `VARCHAR(255)`              | No       |         | `INDEX`, `FK`                                        | `workers(worker_id)` (CASCADE) |
| `cpu_usage`      | `FLOAT`                     | No       |         |                                                      |                            |
| `memory_usage`   | `FLOAT`                     | No       |         |                                                      |                            |
| `jobs_processed` | `INT`                       | No       |         |                                                      |                            |
| `timestamp`      | `DATETIME`                  | No       |         | `INDEX`                                              |                            |

### `scaling_rules`
- **Primary Key:** `id`
- **Description:** Defines rules for auto-scaling workers.

| Column            | Type                                                     | Nullable | Default           | Constraints/Indexes |
|-------------------|----------------------------------------------------------|----------|-------------------|---------------------|
| `id`              | `INT AUTO_INCREMENT`                                     | No       |                   | `PRIMARY KEY`       |
| `condition_type`  | `ENUM('queue_length', 'worker_load', 'time_of_day')`     | No       |                   |                     |
| `threshold_value` | `FLOAT`                                                  | No       |                   |                     |
| `action`          | `ENUM('scale_up', 'scale_down')`                         | No       |                   |                     |
| `worker_count`    | `INT`                                                    | No       |                   |                     |
| `is_active`       | `BOOLEAN`                                                | Yes      | `1`               |                     |
| `created_at`      | `DATETIME`                                               | Yes      | `CURRENT_TIMESTAMP` |                     |

### `worker_processes`
- **Primary Key:** `id`
- **Description:** Manages individual worker processes.

| Column           | Type                                              | Nullable | Default           | Constraints/Indexes                                  |
|------------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|
| `id`             | `VARCHAR(36)`                                     | No       |                   | `PRIMARY KEY`                                        |
| `type`           | `VARCHAR(50)`                                     | No       |                   | `INDEX`                                              |
| `status`         | `ENUM('idle', 'working', 'stopped', 'failed')`    | No       | `idle`            | `INDEX`                                              |
| `pid`            | `INT`                                             | Yes      | `NULL`            |                                                      |
| `memory_limit`   | `INT`                                             | No       | `128`             |                                                      |
| `current_memory` | `INT`                                             | Yes      | `0`               |                                                      |
| `last_heartbeat` | `TIMESTAMP`                                       | Yes      | `NULL`            |                                                      |
| `last_job_id`    | `VARCHAR(36)`                                     | Yes      | `NULL`            |                                                      |
| `created_at`     | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`     | `TIMESTAMP`                                       | Yes      | `NULL` (on update `CURRENT_TIMESTAMP`) |                                      |

### `worker_jobs`
- **Primary Key:** `id`
- **Description:** Represents jobs to be processed by workers.
- **Notes:** `batch_job_id` is `VARCHAR(36)` but `batch_jobs.id` is `INT`. This is a type mismatch for the FK.

| Column         | Type                                                  | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|----------------|-------------------------------------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`           | `VARCHAR(36)`                                         | No       |                   | `PRIMARY KEY`                                        |                                      |
| `process_id`   | `VARCHAR(36)`                                         | Yes      | `NULL`            | `INDEX`, `FK`                                        | `worker_processes(id)` (SET NULL)    |
| `batch_job_id` | `VARCHAR(36)`                                         | Yes      | `NULL`            | `INDEX`, `FK` (Type Mismatch)                        | `batch_jobs(id)` (CASCADE)           |
| `type`         | `VARCHAR(50)`                                         | No       |                   |                                                      |                                      |
| `payload`      | `JSON`                                                | No       |                   |                                                      |                                      |
| `status`       | `ENUM('queued', 'processing', 'completed', 'failed')` | No       | `queued`          | `INDEX`                                              |                                      |
| `attempts`     | `INT`                                                 | No       | `0`               |                                                      |                                      |
| `max_attempts` | `INT`                                                 | No       | `3`               |                                                      |                                      |
| `output`       | `TEXT`                                                | Yes      | `NULL`            |                                                      |                                      |
| `created_at`   | `TIMESTAMP`                                           | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `started_at`   | `TIMESTAMP`                                           | Yes      | `NULL`            |                                                      |                                      |
| `completed_at` | `TIMESTAMP`                                           | Yes      | `NULL`            |                                                      |                                      |

### `batch_jobs`
- **Primary Key:** `id`
- **Description:** Defines batch jobs composed of multiple items.

| Column         | Type                                              | Nullable | Default           | Constraints/Indexes                                  |
|----------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|
| `id`           | `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |
| `type`         | `VARCHAR(50)`                                     | No       |                   | `INDEX`                                              |
| `status`       | `ENUM('pending', 'processing', 'completed', 'failed')` | No       | `pending`         | `INDEX`                                              |
| `payload`      | `JSON`                                            | Yes      | `NULL`            |                                                      |
| `created_at`   | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `started_at`   | `TIMESTAMP`                                       | Yes      | `NULL`            |                                                      |
| `completed_at` | `TIMESTAMP`                                       | Yes      | `NULL`            |                                                      |

### `batch_job_items`
- **Primary Key:** `id`
- **Description:** Individual items within a batch job.

| Column         | Type                                              | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|----------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`           | `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |                            |
| `job_id`       | `INT`                                             | No       |                   | `INDEX`, `FK`                                        | `batch_jobs(id)` (CASCADE) |
| `item_id`      | `VARCHAR(255)`                                    | No       |                   | `INDEX`                                              |                            |
| `status`       | `ENUM('pending', 'processing', 'completed', 'failed')` | No       | `pending`         | `INDEX`                                              |                            |
| `result`       | `JSON`                                            | Yes      | `NULL`            |                                                      |                            |
| `processed_at` | `TIMESTAMP`                                       | Yes      | `NULL`            |                                                      |                            |

### `worker_assignments`
- **Primary Key:** `assignment_id`
- **Description:** Tracks assignment of scheduled events to workers.

| Column         | Type                                              | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|----------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `assignment_id`| `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |                                      |
| `worker_id`    | `VARCHAR(255)`                                    | No       |                   | `INDEX (worker_id, status)`, `FK`                    | `workers(worker_id)` (CASCADE)       |
| `event_id`     | `INT`                                             | No       |                   | `FK`                                                 | `scheduled_content(id)` (CASCADE)    |
| `assigned_at`  | `DATETIME`                                        | No       | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `completed_at` | `DATETIME`                                        | Yes      | `NULL`            |                                                      |                                      |
| `status`       | `ENUM('pending', 'processing', 'completed', 'failed')` | No       | `pending`         | `INDEX (worker_id, status)`                          |                                      |

## Analytics & Logging

### `recommendation_analytics`
- **Primary Key:** `id`
- **Description:** Stores analytics data for content recommendations.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `content_id`  | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `content_items(id)` (CASCADE) |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `event_type`  | `VARCHAR(50)`               | No       |                   | `INDEX`                                              |                            |
| `score`       | `FLOAT`                     | Yes      | `NULL`            |                                                      |                            |
| `details`     | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `user_preferences`
- **Primary Key:** `id`
- **Description:** Stores user-specific preferences.

| Column         | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`           | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`      | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`      | `INT`                       | No       |                   | `UNIQUE (user_id, preference_key)`, `FK`             | `users(id)` (CASCADE)      |
| `preference_key` | `VARCHAR(100)`            | No       |                   | `UNIQUE (user_id, preference_key)`                   |                            |
| `value`        | `TEXT`                      | No       |                   |                                                      |                            |
| `created_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `user_similarity`
- **Primary Key:** `id`
- **Description:** Stores similarity scores between users for recommendation purposes.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id_1`   | `INT`                       | No       |                   | `UNIQUE (user_id_1, user_id_2)`, `FK`                | `users(id)` (CASCADE)      |
| `user_id_2`   | `INT`                       | No       |                   | `UNIQUE (user_id_1, user_id_2)`, `FK`                | `users(id)` (CASCADE)      |
| `score`       | `FLOAT`                     | No       |                   |                                                      |                            |
| `calculated_at` | `DATETIME`                | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `user_content_interactions`
- **Primary Key:** `id`
- **Description:** Tracks user interactions with content items.

| Column          | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`            | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`       | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`       | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `users(id)` (CASCADE)      |
| `content_id`    | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `content_items(id)` (CASCADE) |
| `interaction_type` | `VARCHAR(50)`            | No       |                   | `INDEX`                                              |                            |
| `value`         | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |                            |
| `created_at`    | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `user_behavior_events`
- **Primary Key:** `id`
- **Description:** Logs various user behavior events.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `session_id`  | `VARCHAR(255)`              | Yes      | `NULL`            | `INDEX`                                              |                            |
| `event_name`  | `VARCHAR(100)`              | No       |                   | `INDEX`                                              |                            |
| `event_data`  | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `recommendation_feedback`
- **Primary Key:** `id`
- **Description:** Stores user feedback on recommendations.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|--------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `user_id`          | `INT`                       | No       |                   | `FK`                                                 | `users(id)` (CASCADE)      |
| `content_id`       | `INT`                       | No       |                   | `FK`                                                 | `content_items(id)` (CASCADE) |
| `recommendation_source` | `VARCHAR(100)`         | Yes      | `NULL`            |                                                      |                            |
| `feedback_type`    | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `rating`           | `TINYINT`                   | Yes      | `NULL`            |                                                      |                            |
| `comments`         | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`       | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `ab_tests`
- **Primary Key:** `id`
- **Description:** Defines A/B tests.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `status`      | `VARCHAR(20)`               | No       | `draft`           |                                                      |
| `start_date`  | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `end_date`    | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `ab_test_assignments`
- **Primary Key:** `id`
- **Description:** Assigns users to A/B test variations.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `test_id`     | `INT`                       | No       |                   | `FK`                                                 | `ab_tests(id)` (CASCADE)   |
| `user_id`     | `INT`                       | No       |                   | `FK`                                                 | `users(id)` (CASCADE)      |
| `variation_name` | `VARCHAR(100)`           | No       |                   |                                                      |                            |
| `assigned_at` | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
|               |                             |          |                   | `UNIQUE (test_id, user_id)`                          |                            |

### `ab_test_conversions`
- **Primary Key:** `id`
- **Description:** Tracks conversions for A/B tests.

| Column          | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`            | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `assignment_id` | `INT`                       | No       |                   | `FK`                                                 | `ab_test_assignments(id)` (CASCADE) |
| `event_name`    | `VARCHAR(100)`              | No       |                   |                                                      |                            |
| `converted_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `analytics_events`
- **Primary Key:** `id`
- **Description:** General table for various analytics events.
- **Notes:** Defined in `2025_05_15_140000_create_analytics_events_table.php`. An earlier, simpler version was in `2025_05_14_000000_create_recommendation_tables.php`.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `session_id`  | `VARCHAR(255)`              | Yes      | `NULL`            | `INDEX`                                              |                            |
| `event_type`  | `VARCHAR(100)`              | No       |                   | `INDEX`                                              |                            |
| `event_data`  | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `ip_address`  | `VARCHAR(45)`               | Yes      | `NULL`            |                                                      |                            |
| `user_agent`  | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |                            |

### `personalization_events`
- **Primary Key:** `id`
- **Description:** Logs events related to content personalization.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `content_id`  | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `content_items(id)` (SET NULL) |
| `event_type`  | `VARCHAR(50)`               | No       |                   | `INDEX`                                              |                            |
| `details`     | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `ai_api_usage`
- **Primary Key:** `id`
- **Description:** Tracks usage of AI-related APIs.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `BIGINT UNSIGNED`           | Yes      | `NULL`            | `FK`                                                 | `sites(id)` (CASCADE)      |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `api_name`    | `VARCHAR(100)`              | No       |                   |                                                      |                            |
| `request_data`| `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `response_data`| `JSON`                     | Yes      | `NULL`            |                                                      |                            |
| `tokens_used` | `INT`                       | Yes      | `NULL`            |                                                      |                            |
| `status_code` | `INT`                       | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `presence_events`
- **Primary Key:** `id`
- **Description:** Logs user presence events (e.g., joining/leaving a page).

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `user_id`     | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `users(id)` (CASCADE)      |
| `event_type`  | `VARCHAR(50)`               | No       |                   | `INDEX`                                              |                            |
| `target_type` | `VARCHAR(50)`               | Yes      | `NULL`            |                                                      |                            |
| `target_id`   | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |                            |
| `details`     | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `analytics_metrics`
- **Primary Key:** `id`
- **Description:** Stores aggregated analytics metrics.
- **Notes:** `site_id` is `INT UNSIGNED` but `sites.id` is `BIGINT UNSIGNED`.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `site_id`     | `INT UNSIGNED`              | No       |                   | `INDEX`, `FK` (Type Mismatch)                        | `sites(id)` (CASCADE)      |
| `metric_name` | `VARCHAR(100)`              | No       |                   | `UNIQUE (site_id, metric_name, metric_date)`         |                            |
| `metric_value`| `DECIMAL(15,2)`             | No       |                   |                                                      |                            |
| `metric_date` | `DATE`                      | No       |                   | `UNIQUE (site_id, metric_name, metric_date)`         |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `analytics_daily_summaries`
- **Primary Key:** `id`
- **Description:** Daily summaries of analytics data.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|--------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `summary_date`     | `DATE`                      | No       |                   | `UNIQUE`                                             |                            |
| `total_page_views` | `INT UNSIGNED`              | No       | `0`               |                                                      |                            |
| `unique_visitors`  | `INT UNSIGNED`              | No       | `0`               |                                                      |                            |
| `average_time_on_site` | `INT UNSIGNED`          | No       | `0`               |                                                      |                            |
| `bounce_rate`      | `DECIMAL(5,2)`              | No       | `0.00`            |                                                      |                            |
| `top_referrers`    | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `top_pages`        | `JSON`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`       | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `analytics_content_views`
- **Primary Key:** `id`
- **Description:** Tracks views for specific content items.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `content_id`  | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `content_items(id)` (CASCADE) |
| `view_date`   | `DATE`                      | No       |                   | `INDEX`                                              |                            |
| `view_count`  | `INT UNSIGNED`              | No       | `0`               |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
|               |                             |          |                   | `UNIQUE (content_id, view_date)`                     |                            |

### `analytics_failures`
- **Primary Key:** `id`
- **Description:** Logs failures in the analytics data processing pipeline.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `source`      | `VARCHAR(100)`              | No       |                   |                                                      |
| `error_message`| `TEXT`                     | No       |                   |                                                      |
| `payload`     | `JSON`                      | Yes      | `NULL`            |                                                      |
| `failed_at`   | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |

### `version_analytics`
- **Primary Key:** `id`
- **Description:** Tracks analytics events specific to content versions.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `version_id`  | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `versions(id)`             |
| `event_type`  | `VARCHAR(50)`               | No       |                   | `INDEX`                                              |                            |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX (user_id, version_id)`, `FK`                  | `users(id)`                |
| `ip_address`  | `VARCHAR(45)`               | Yes      | `NULL`            |                                                      |                            |
| `user_agent`  | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `session_id`  | `VARCHAR(64)`               | Yes      | `NULL`            | `INDEX` (Added by `2025_05_17_001100_enhance_version_analytics_for_metrics.php`) |                            |
| `time_spent`  | `INT UNSIGNED`              | Yes      | `NULL`            | (Added by `2025_05_17_001100_enhance_version_analytics_for_metrics.php`) |                            |
| `page_exit`   | `BOOLEAN`                   | Yes      | `0`               | (Added by `2025_05_17_001100_enhance_version_analytics_for_metrics.php`) |                            |
| `referrer`    | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |                            |

### `version_analytics_metrics`
- **Primary Key:** `id`
- **Description:** Aggregated metrics for content versions.

| Column                 | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|------------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`                   | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `version_id`           | `INT`                       | No       |                   | `UNIQUE`, `FK`                                       | `versions(id)`             |
| `view_count`           | `INT UNSIGNED`              | Yes      | `0`               |                                                      |                            |
| `unique_views`         | `INT UNSIGNED`              | Yes      | `0`               |                                                      |                            |
| `average_time_spent`   | `INT UNSIGNED`              | Yes      | `0`               |                                                      |                            |
| `bounce_rate`          | `DECIMAL(5,2)`              | Yes      | `0.00`            |                                                      |                            |
| `last_viewed_at`       | `TIMESTAMP`                 | Yes      | `NULL`            |                                                      |                            |
| `created_at`           | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`           | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `audit_log`
- **Primary Key:** `id`
- **Description:** General audit trail for system and user actions.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `user_id`     | `INT`                       | Yes      | `NULL`            | `INDEX` (Implied FK to `users(id)`)                  |
| `action_type` | `VARCHAR(100)`              | No       |                   | `INDEX`                                              |
| `entity_type` | `VARCHAR(100)`              | Yes      | `NULL`            | `INDEX (entity_type, entity_id)`                     |
| `entity_id`   | `INT`                       | Yes      | `NULL`            | `INDEX (entity_type, entity_id)`                     |
| `ip_address`  | `VARCHAR(45)`               | Yes      | `NULL`            |                                                      |
| `user_agent`  | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `details`     | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `status`      | `ENUM('success', 'failure')`| No       |                   |                                                      |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |

## Multi-site & Federation

### `sites`
- **Primary Key:** `id`
- **Description:** Defines different sites managed by the CMS.
- **Notes:** Defined by `2025_05_15_000000_create_sites_table.php`. Another migration `2025_05_15_130100_create_sites_table.php` creates `site_users` and `site_content` with `site_id INT UNSIGNED` which mismatches this `BIGINT UNSIGNED` PK.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `BIGINT UNSIGNED AUTO_INCREMENT` | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `domain`      | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `is_active`   | `BOOLEAN`                   | No       | `1`               |                                                      |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `site_users`
- **Primary Key:** (`site_id`, `user_id`)
- **Description:** Links users to sites (many-to-many).
- **Notes:** `site_id` is `INT UNSIGNED`, but `sites.id` is `BIGINT UNSIGNED`.

| Column    | Type           | Nullable | Default | Constraints/Indexes                                  | Foreign Key To             |
|-----------|----------------|----------|---------|------------------------------------------------------|----------------------------|
| `site_id` | `INT UNSIGNED` | No       |         | `PRIMARY KEY`, `FK` (Type Mismatch)                  | `sites(id)` (CASCADE)      |
| `user_id` | `INT`          | No       |         | `PRIMARY KEY`, `FK`                                  | `users(id)` (CASCADE)      |

### `site_content`
- **Primary Key:** (`site_id`, `content_id`)
- **Description:** Links content items to sites (many-to-many).
- **Notes:** `site_id` is `INT UNSIGNED`, but `sites.id` is `BIGINT UNSIGNED`.

| Column       | Type           | Nullable | Default | Constraints/Indexes                                  | Foreign Key To             |
|--------------|----------------|----------|---------|------------------------------------------------------|----------------------------|
| `site_id`    | `INT UNSIGNED` | No       |         | `PRIMARY KEY`, `FK` (Type Mismatch)                  | `sites(id)` (CASCADE)      |
| `content_id` | `INT`          | No       |         | `PRIMARY KEY`, `FK`                                  | `content_items(id)` (CASCADE) |

### `federation_nodes`
- **Primary Key:** `id`
- **Description:** Stores information about federated CMS nodes.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `node_url`    | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `node_name`   | `VARCHAR(100)`              | Yes      | `NULL`            |                                                      |
| `api_key`     | `VARCHAR(255)`              | No       |                   |                                                      |
| `status`      | `VARCHAR(20)`               | No       | `inactive`        |                                                      |
| `last_sync`   | `DATETIME`                  | Yes      | `NULL`            |                                                      |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `federation_outgoing`
- **Primary Key:** `id`
- **Description:** Tracks outgoing content/data for federation.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `node_id`     | `INT`                       | No       |                   | `FK`                                                 | `federation_nodes(id)` (CASCADE) |
| `entity_type` | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `entity_id`   | `VARCHAR(255)`              | No       |                   |                                                      |                            |
| `status`      | `VARCHAR(20)`               | No       | `pending`         |                                                      |                            |
| `attempts`    | `INT`                       | No       | `0`               |                                                      |                            |
| `last_attempt`| `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `federation_incoming`
- **Primary Key:** `id`
- **Description:** Tracks incoming content/data from federated nodes.

| Column          | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`            | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `source_node_id`| `INT`                       | No       |                   | `FK`                                                 | `federation_nodes(id)` (CASCADE) |
| `entity_type`   | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `source_entity_id` | `VARCHAR(255)`           | No       |                   |                                                      |                            |
| `local_entity_id` | `VARCHAR(255)`           | Yes      | `NULL`            |                                                      |                            |
| `payload`       | `JSON`                      | No       |                   |                                                      |                            |
| `status`        | `VARCHAR(20)`               | No       | `pending`         |                                                      |                            |
| `processed_at`  | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `created_at`    | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |

### `federation_conflicts`
- **Primary Key:** `id`
- **Description:** Logs conflicts arising during federation.

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`              | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `incoming_id`     | `INT`                       | No       |                   | `FK`                                                 | `federation_incoming(id)` (CASCADE) |
| `conflict_type`   | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `details`         | `TEXT`                      | No       |                   |                                                      |                            |
| `resolved_status` | `VARCHAR(20)`               | No       | `unresolved`      |                                                      |                            |
| `resolved_at`     | `DATETIME`                  | Yes      | `NULL`            |                                                      |                            |
| `created_at`      | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |

## Realtime & Presence

### `user_sessions`
- **Primary Key:** `id`
- **Description:** Tracks active user sessions for real-time features.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `VARCHAR(255)`              | No       |                   | `PRIMARY KEY`                                        |                            |
| `user_id`     | `INT`                       | No       |                   | `INDEX`, `FK`                                        | `users(id)` (CASCADE)      |
| `ip_address`  | `VARCHAR(45)`               | Yes      | `NULL`            |                                                      |                            |
| `user_agent`  | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `last_active` | `DATETIME`                  | No       |                   |                                                      |                            |
| `created_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `document_permissions`
- **Primary Key:** (`document_id`, `user_id`)
- **Description:** Manages real-time document access permissions.

| Column        | Type                        | Nullable | Default | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|---------|------------------------------------------------------|----------------------------|
| `document_id` | `VARCHAR(255)`              | No       |         | `PRIMARY KEY`                                        |                            |
| `user_id`     | `INT`                       | No       |         | `PRIMARY KEY`, `FK`                                  | `users(id)` (CASCADE)      |
| `permission_level` | `VARCHAR(20)`          | No       |         |                                                      |                            |
| `granted_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

### `presence_tracking`
- **Primary Key:** `id`
- **Description:** Tracks user presence on specific entities (e.g., documents).

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `user_id`     | `INT`                       | No       |                   | `FK`                                                 | `users(id)` (CASCADE)      |
| `entity_type` | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `entity_id`   | `VARCHAR(255)`              | No       |                   |                                                      |                            |
| `last_seen`   | `DATETIME`                  | No       |                   |                                                      |                            |
|               |                             |          |                   | `UNIQUE (user_id, entity_type, entity_id)`           |                            |

## Notifications (General System)

### `notifications`
- **Primary Key:** `id`
- **Description:** General notification system for users.

| Column           | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|------------------|-----------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`            | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                      |
| `user_id`       | `INT`                       | No       |                   | `FK`                                                 | `users(id)`                          |
| `category_id`   | `INT`                       | Yes      | `NULL`            | `FK`                                                 | `notification_categories(id)`        |
| `title`         | `VARCHAR(255)`              | No       |                   |                                                      |                                      |
| `message`       | `TEXT`                      | No       |                   |                                                      |                                      |
| `is_read`       | `BOOLEAN`                   | Yes      | `0`               |                                                      |                                      |
| `type`          | `VARCHAR(50)`               | Yes      | `NULL`            | `INDEX`                                              |                                      |
| `view_count`    | `INT`                       | No       | `0`               |                                                      |                                      |
| `created_at`    | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `updated_at`    | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                      |
| `last_viewed_at`| `TIMESTAMP`                 | Yes      | `NULL`            |                                                      |                                      |

### `read_receipts`
- **Primary Key:** `id`
- **Description:** Tracks notification read receipts
- **Relationships:**
  - `notification_id` references `notifications(id)`
  - `user_id` references `users(id)`

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`             | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `notification_id`| `INT`                       | No       |                   | `INDEX`, `FK`                                        |
| `user_id`        | `INT`                       | No       |                   | `INDEX`, `FK`                                        |
| `read_at`        | `TIMESTAMP`                 | No       | `CURRENT_TIMESTAMP` |                                                      |

### `notification_templates`
- **Primary Key:** `template_id`
- **Description:** Stores notification templates

| Column            | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|-------------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `template_id`    | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`           | `VARCHAR(100)`              | No       |                   |                                                      |
| `description`    | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `type`           | `VARCHAR(50)`               | No       |                   | `INDEX`                                              |
| `subject_template`| `TEXT`                      | No       |                   |                                                      |
| `body_template`  | `TEXT`                      | No       |                   |                                                      |
| `variables`      | `JSON`                      | Yes      | `NULL`            |                                                      |
| `channels`       | `JSON`                      | No       |                   |                                                      |

### `notification_categories`
- **Primary Key:** `id`
- **Description:** Categories for general notifications.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `name`        | `VARCHAR(100)`              | No       |                   |                                                      |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |
| `color_code`  | `VARCHAR(7)`                | Yes      | `#3498db`         |                                                      |
| `is_system`   | `BOOLEAN`                   | Yes      | `0`               |                                                      |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `user_notification_preferences`
- **Primary Key:** `id`
- **Description:** User preferences for receiving notifications.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                                      |
| `user_id`     | `INT`                       | No       |                   | `UNIQUE (user_id, category_id)`, `FK`                | `users(id)`                          |
| `category_id` | `INT`                       | Yes      | `NULL`            | `UNIQUE (user_id, category_id)`, `FK`                | `notification_categories(id)`        |
| `channels`    | `JSON`                      | No       |                   |                                                      |                                      |
| `frequency`   | `VARCHAR(20)`               | Yes      | `immediate`       |                                                      |                                      |
| `created_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `updated_at`  | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                                      |

### `notification_preference_cache`
- **Primary Key:** `user_id`
- **Description:** Caches user notification preferences.
- **Notes:** `user_id` is `VARCHAR(255)`, but `users.id` is `INT`. Type mismatch.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `user_id`     | `VARCHAR(255)`              | No       |                   | `PRIMARY KEY`                                        |
| `preferences` | `JSON`                      | No       |                   |                                                      |
| `cached_at`   | `DATETIME`                  | No       | `CURRENT_TIMESTAMP` |                                                      |
| `expires_at`  | `DATETIME`                  | No       |                   | `INDEX`                                              |

## Clients

### `clients`
- **Primary Key:** `id`
- **Description:** Stores client information.

| Column       | Type                                       | Nullable | Default           | Constraints/Indexes                                  |
|--------------|--------------------------------------------|----------|-------------------|------------------------------------------------------|
| `id`         | `INT AUTO_INCREMENT`                       | No       |                   | `PRIMARY KEY`                                        |
| `name`       | `VARCHAR(255)`                             | No       |                   |                                                      |
| `email`      | `VARCHAR(255)`                             | Yes      | `NULL`            | `INDEX`                                              |
| `phone`      | `VARCHAR(50)`                              | Yes      | `NULL`            |                                                      |
| `address`    | `TEXT`                                     | Yes      | `NULL`            |                                                      |
| `status`     | `ENUM('active', 'inactive', 'pending')`    | Yes      | `active`          | `INDEX`                                              |
| `created_at` | `TIMESTAMP`                                | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at` | `TIMESTAMP`                                | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `client_activities`
- **Primary Key:** `id`
- **Description:** Tracks activities related to clients.
- **Notes:** A similar table `client_activity_logs` exists (from `phase3/0003_create_client_activity_logs.php`), which is simpler and likely redundant. This one is more comprehensive.

| Column             | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|--------------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`               | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `client_id`        | `INT`                       | No       |                   | `FK`                                                 | `clients(id)` (CASCADE)    |
| `user_id`          | `INT`                       | Yes      | `NULL`            | `FK`                                                 | `users(id)` (SET NULL)     |
| `activity_type`    | `VARCHAR(50)`               | No       |                   |                                                      |                            |
| `activity_details` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `ip_address`       | `VARCHAR(45)`               | Yes      | `NULL`            |                                                      |                            |
| `user_agent`       | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `created_at`       | `TIMESTAMP`                 | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |

## Miscellaneous

### `migrations`
- **Primary Key:** `id`
- **Description:** Tracks executed database migrations.

| Column      | Type                        | Nullable | Default           | Constraints/Indexes                                  |
|-------------|-----------------------------|----------|-------------------|------------------------------------------------------|
| `id`        | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |
| `migration` | `VARCHAR(255)`              | No       |                   | `UNIQUE`                                             |
| `batch`     | `INT`                       | No       |                   |                                                      |
| `applied_at`| `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |

### `media`
- **Primary Key:** `id`
- **Description:** Stores information about uploaded media files.

| Column        | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|---------------|-----------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`          | `INT AUTO_INCREMENT`        | No       |                   | `PRIMARY KEY`                                        |                            |
| `uploader_id` | `INT`                       | Yes      | `NULL`            | `INDEX`, `FK`                                        | `users(id)` (SET NULL)     |
| `file_name`   | `VARCHAR(255)`              | No       |                   |                                                      |                            |
| `file_path`   | `VARCHAR(512)`              | No       |                   |                                                      |                            |
| `file_type`   | `VARCHAR(100)`              | No       |                   | `INDEX`                                              |                            |
| `file_size`   | `INT`                       | No       |                   |                                                      |                            |
| `title`       | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |                            |
| `caption`     | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `alt_text`    | `VARCHAR(255)`              | Yes      | `NULL`            |                                                      |                            |
| `description` | `TEXT`                      | Yes      | `NULL`            |                                                      |                            |
| `uploaded_at` | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`  | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `shifts`
- **Primary Key:** `shift_id`
- **Description:** Manages worker shifts.

| Column       | Type                                                      | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|--------------|-----------------------------------------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `shift_id`   | `INT AUTO_INCREMENT`                                      | No       |                   | `PRIMARY KEY`                                        |                            |
| `worker_id`  | `VARCHAR(255)`                                            | No       |                   | `INDEX`, `FK`                                        | `workers(worker_id)` (CASCADE) |
| `start_time` | `DATETIME`                                                | No       |                   | `INDEX`                                              |                            |
| `end_time`   | `DATETIME`                                                | No       |                   |                                                      |                            |
| `status`     | `ENUM('scheduled', 'in_progress', 'completed', 'cancelled')` | No       | `scheduled`       | `INDEX`                                              |                            |
| `location`   | `VARCHAR(255)`                                            | Yes      | `NULL`            |                                                      |                            |
| `notes`      | `TEXT`                                                    | Yes      | `NULL`            |                                                      |                            |
| `created_at` | `DATETIME`                                                | No       | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at` | `DATETIME`                                                | Yes      | `NULL` (on update `CURRENT_TIMESTAMP`) |                                      |                            |

### `settings`
- **Primary Key:** `id`
- **Description:** Key-value store for application settings.

| Column       | Type                                              | Nullable | Default           | Constraints/Indexes                                  |
|--------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|
| `id`         | `INT AUTO_INCREMENT`                              | No       |                   | `PRIMARY KEY`                                        |
| `key`        | `VARCHAR(255)`                                    | No       |                   | `UNIQUE`, `INDEX`                                    |
| `value`      | `TEXT`                                            | Yes      | `NULL`            |                                                      |
| `data_type`  | `ENUM('string', 'number', 'boolean', 'json')`     | No       | `string`          |                                                      |
| `created_at` | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` |                                                      |
| `updated_at` | `TIMESTAMP`                                       | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |

### `calendar_connections`
- **Primary Key:** `id`
- **Description:** Stores connections to external calendar services.

| Column          | Type                               | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To             |
|-----------------|------------------------------------|----------|-------------------|------------------------------------------------------|----------------------------|
| `id`            | `INT AUTO_INCREMENT`               | No       |                   | `PRIMARY KEY`                                        |                            |
| `user_id`       | `INT`                              | No       |                   | `FK`                                                 | `users(id)` (CASCADE)      |
| `provider_type` | `ENUM('google', 'outlook', 'ical')`| No       |                   |                                                      |                            |
| `access_token`  | `TEXT`                             | No       |                   |                                                      |                            |
| `refresh_token` | `TEXT`                             | Yes      | `NULL`            |                                                      |                            |
| `expires_at`    | `DATETIME`                         | Yes      | `NULL`            |                                                      |                            |
| `sync_enabled`  | `BOOLEAN`                          | Yes      | `0`               |                                                      |                            |
| `last_sync_at`  | `DATETIME`                         | Yes      | `NULL`            |                                                      |                            |
| `created_at`    | `TIMESTAMP`                        | Yes      | `CURRENT_TIMESTAMP` |                                                      |                            |
| `updated_at`    | `TIMESTAMP`                        | Yes      | `CURRENT_TIMESTAMP` (on update) |                                      |                            |

### `content_distribution`
- **Primary Key:** `id`
- **Description:** Manages the distribution of content to different channels or sites.

| Column       | Type                                              | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|--------------|---------------------------------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`         | `BIGINT AUTO_INCREMENT`                           | No       |                   | `PRIMARY KEY`                                        |                                      |
| `content_id` | `INT`                                             | No       |                   | `INDEX`, `FK`                                        | `content_items(id)` (CASCADE)        |
| `created_at` | `DATETIME`                                        | Yes      | `CURRENT_TIMESTAMP` |                                                      |                                      |
| `status`     | `ENUM('pending', 'processing', 'completed', 'failed')` | Yes      | `pending`         | `INDEX`                                              |                                      |
| `metadata`   | `TEXT`                                            | Yes      | `NULL`            |                                                      |                                      |

### `distributed_content`
- **Primary Key:** `id`
- **Description:** Tracks content that has been distributed.

| Column          | Type                        | Nullable | Default | Constraints/Indexes                                  | Foreign Key To                       |
|-----------------|-----------------------------|----------|---------|------------------------------------------------------|--------------------------------------|
| `id`            | `BIGINT AUTO_INCREMENT`     | No       |         | `PRIMARY KEY`                                        |                                      |
| `distribution_id` | `BIGINT`                  | No       |         | `INDEX`, `UNIQUE (distribution_id, site_id)`, `FK`   | `content_distribution(id)` (CASCADE) |
| `site_id`       | `BIGINT UNSIGNED`           | No       |         | `INDEX`, `UNIQUE (distribution_id, site_id)`, `FK`   | `sites(id)` (CASCADE)                |
| `version_id`    | `INT`                       | No       |         | `FK`                                                 | `versions(id)` (CASCADE)             |
| `synced_at`     | `DATETIME`                  | Yes      | `NULL`  |                                                      |                                      |

### `distribution_conflicts`
- **Primary Key:** `id`
- **Description:** Logs conflicts encountered during content distribution.

| Column                | Type                        | Nullable | Default | Constraints/Indexes                                  | Foreign Key To                       |
|-----------------------|-----------------------------|----------|---------|------------------------------------------------------|--------------------------------------|
| `id`                  | `BIGINT AUTO_INCREMENT`     | No       |         | `PRIMARY KEY`                                        |                                      |
| `distribution_id`     | `BIGINT`                    | No       |         | `INDEX`, `FK`                                        | `content_distribution(id)` (CASCADE) |
| `conflict_details`    | `TEXT`                      | No       |         |                                                      |                                      |
| `resolution_strategy` | `VARCHAR(50)`               | Yes      | `NULL`  |                                                      |                                      |

### `distribution_status`
- **Primary Key:** `id`
- **Description:** Tracks the status of content distribution processes.

| Column          | Type                        | Nullable | Default           | Constraints/Indexes                                  | Foreign Key To                       |
|-----------------|-----------------------------|----------|-------------------|------------------------------------------------------|--------------------------------------|
| `id`            | `BIGINT AUTO_INCREMENT`     | No       |                   | `PRIMARY KEY`                                        |                                      |
| `distribution_id` | `BIGINT`                  | No       |                   | `INDEX`, `FK`                                        | `content_distribution(id)` (CASCADE) |
| `status`        | `VARCHAR(50)`               | No       |                   |                                                      |                                      |
| `message`       | `TEXT`                      | Yes      | `NULL`            |                                                      |                                      |
| `updated_at`    | `DATETIME`                  | Yes      | `CURRENT_TIMESTAMP` | `INDEX`                                              |                                      |
