<?php
/**
 * Centralized table reference registry
 * 
 * Provides single source of truth for all database table names
 * and relationships to prevent hardcoded strings in application
 */
class TableRegistry {
    // Core Tables
    const VERSIONS = 'versions';
    const VERSION_CONTENT = 'version_content';
    const VERSION_METADATA = 'version_metadata';
    const WORKFLOWS = 'workflows';
    const STATUS_TRANSITIONS = 'status_transitions';
    const ANALYTICS_MONTHLY_SUMMARY = 'analytics_monthly_summary';
    const WORKFLOW_VERSION_SNAPSHOTS = 'workflow_version_snapshots';
    const VERSION_ANALYTICS = 'version_analytics';

    // Multi-Tenant Tables
    const TENANTS = 'tenants';
    const CONTENT_PAGES = 'content_pages';
    const CONTENT_BLOCKS = 'content_blocks';
    const TENANT_ANALYTICS_EVENTS = 'tenant_analytics_events';
    const USER_SITES = 'user_sites';

    // System Tables
    const SYSTEM_SCHEDULER_LOG = 'system_scheduler_log';
    const SYSTEM_ALERTS = 'system_alerts';
    const SYSTEM_TASKS = 'system_tasks';
    const WORKERS = 'workers';
    const WORKER_METRICS = 'worker_metrics';
    const RATE_LIMITS = 'rate_limits';

    // Content Management
    const CONTENT_TYPES = 'content_types';
    const CONTENTS = 'contents';
    const CONTENT_FLAGS = 'content_flags';
    const CONTENT_SCHEDULES = 'content_schedules';
    const CONTENT_WORKFLOW = 'content_workflow';
    const CONTENT_WORKFLOW_HISTORY = 'content_workflow_history';

    // Relationships mapping
    public static function getRelationships(): array {
        return [
            self::VERSIONS => [
                'has_many' => [
                    self::VERSION_CONTENT,
                    self::VERSION_METADATA,
                    self::VERSION_ANALYTICS
                ],
                'belongs_to' => ['contents']
            ],
            self::WORKFLOWS => [
                'has_many' => [
                    self::STATUS_TRANSITIONS,
                    self::WORKFLOW_VERSION_SNAPSHOTS
                ]
            ]
        ];
    }

    // Get all tables
    public static function getAllTables(): array {
        return [
            // Core Tables
            self::VERSIONS,
            self::VERSION_CONTENT,
            self::VERSION_METADATA,
            // ... (remaining tables)
        ];
    }
}
