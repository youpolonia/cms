<?php

require_once __DIR__ . '/../../config.php';

/**
 * Tenant Isolation Migration
 * Creates core tables with tenant_id foreign keys
 */
class Migration_0001_TenantIsolation {
    public static function up() {
        $db = \core\Database::connection();

        // Core Tenant Tables
        $db->exec("CREATE TABLE tenants (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            domain TEXT UNIQUE NOT NULL,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $db->exec("CREATE TABLE tenant_metrics (
            tenant_id INTEGER NOT NULL,
            storage_used INTEGER DEFAULT 0,
            api_calls INTEGER DEFAULT 0,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )");

        // Content Management Tables
        $db->exec("CREATE TABLE content_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            title TEXT NOT NULL,
            slug TEXT NOT NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )");

        // User Management Tables
        $db->exec("CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            username TEXT UNIQUE NOT NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id)
        )");

        // Security Tables
        $db->exec("CREATE TABLE audit_log (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tenant_id INTEGER NOT NULL,
            user_id INTEGER,
            action TEXT NOT NULL,
            FOREIGN KEY (tenant_id) REFERENCES tenants(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )");

        // System Tables
        $db->exec("CREATE TABLE migrations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public static function down() {
        $db = \core\Database::connection();
        $tables = [
            'audit_log', 'users', 'content_pages',
            'tenant_metrics', 'tenants', 'migrations'
        ];

        foreach ($tables as $table) {
            $db->exec("DROP TABLE IF EXISTS $table");
        }
    }
}
