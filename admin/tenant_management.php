<?php
/**
 * Tenant Management Dashboard
 * 
 * Displays:
 * - Tenant quotas
 * - Current usage
 * - Migration status
 * 
 * Requires admin privileges
 */

declare(strict_types=1);

require_once __DIR__.'/../includes/core/adminauth.php';
AdminAuth::verify();

// Get tenant data from API
$tenantData = json_decode(file_get_contents(
    'http://localhost/api/migrations/tenant/status'
), true);

// Display quota/usage dashboard
echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Tenant Management</title>
    <style>
        .tenant-card { 
            border: 1px solid #ddd; 
            padding: 15px; 
            margin: 10px; 
            border-radius: 5px;
        }
        .quota-bar {
            height: 20px;
            background: #f0f0f0;
            margin: 5px 0;
        }
        .quota-progress {
            height: 100%;
            background: #4CAF50;
        }
    </style>
</head>
<body>
    <h1>Tenant Management</h1>
    
    <div class="tenant-card">
        <h2>{$tenantData['name']}</h2>
        <p>Storage: {$tenantData['storage_used']}MB / {$tenantData['storage_quota']}MB</p>
        <div class="quota-bar">
            <div class="quota-progress" style="width: {$tenantData['storage_percent']}%"></div>
        </div>

        <h3>Migration Status</h3>
        <p>Current Version: {$tenantData['migration_version']}</p>
        <button onclick="runMigration()">Run Migrations</button>
        <button onclick="rollbackMigration()">Rollback</button>
    </div>

    <script>
    function runMigration() {
        fetch('/api/migrations/tenant/migrate', {method: 'POST'})
            .then(response => response.json())
            .then(data => alert(data.success ? 'Success' : 'Failed'));
    }
    
    function rollbackMigration() {
        fetch('/api/migrations/tenant/rollback', {method: 'POST'})
            .then(response => response.json())
            .then(data => alert(data.success ? 'Success' : 'Failed'));
    }
    </script>
</body>
</html>
HTML;
