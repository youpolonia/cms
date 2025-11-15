START TRANSACTION;
-- Backup commands (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS backup_db;
-- CREATE TABLE backup_db.migrations_backup AS SELECT * FROM migrations;
-- Preserving users from 2025_05_02_123950_create_test_users_table.php
-- DROP redundant duplicates for users
-- Preserving analytics_exports from 2025_04_30_001200_create_analytics_exports_table.php
-- DROP redundant duplicates for analytics_exports
-- Preserving content_version_diffs from 2025_04_25_000000_create_content_version_diffs_table.php
-- DROP redundant duplicates for content_version_diffs
-- Preserving moderation_queue from 2025_05_01_012900_create_moderation_queue_table.php
-- DROP redundant duplicates for moderation_queue
-- Preserving moderation_analytics from 2025_05_01_015400_create_moderation_analytics_table.php
-- DROP redundant duplicates for moderation_analytics
-- Preserving analytics_snapshots from 2025_04_30_001100_create_analytics_snapshots_table.php
-- DROP redundant duplicates for analytics_snapshots
-- Preserving version_analytics from 2025_05_02_213900_create_version_analytics_table.php
-- DROP redundant duplicates for version_analytics

-- Cleanup migrations table
DELETE FROM migrations WHERE migration LIKE '2025_04_30%' AND migration != '2025_04_30_000000_create_version_comparisons_table';
DELETE FROM migrations WHERE migration LIKE '2025_05_%' AND migration NOT IN (
    '2025_05_02_123950_create_test_users_table',
    '2025_05_01_012900_create_moderation_queue_table',
    '2025_05_01_015400_create_moderation_analytics_table',
    '2025_05_02_213900_create_version_analytics_table'
);
COMMIT;