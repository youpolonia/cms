<?php
/**
 * Debug script to verify analytics tables and dependencies
 */

if (!defined('DEV_MODE')) {
    http_response_code(500);
    echo 'Configuration error';
    return;
}
if (!DEV_MODE) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Forbidden in production";
    return;
}

header('Content-Type: text/html; charset=utf-8');

try {
    require_once __DIR__.'/core/database.php';
    
    $db = \core\Database::connection();
    $tables = [
        'analytics_click_events',
        'analytics_test_endpoints',
        'analytics_aggregates',
        'tenants', // Required by click_events
        'analytics_page_views' // Required by click_events
    ];

    $results = [];
    foreach ($tables as $table) {
        try {
            $exists = $db->query("SELECT 1 FROM $table LIMIT 1") !== false;
            $results[$table] = $exists ? '✅ Exists' : '❌ Missing';
        } catch (PDOException $e) {
            $results[$table] = '❌ Missing (' . htmlspecialchars($e->getMessage()) . ')';
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Table Status Check</title>
    </head>
    <body>
        <h1>Analytics Tables Status</h1>
        <pre>
    <?php
    foreach ($results as $table => $status) {
        echo "- $table: $status\n";
    }
    ?>
        </pre>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    http_response_code(500);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Error</title>
    </head>
    <body>
        <h1>Debug Script Error</h1>
        <pre><?php echo htmlspecialchars($e->getMessage()); ?></pre>
    </body>
    </html>
    <?php
}