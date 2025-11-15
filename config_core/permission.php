<?php
/**
 * Pure PHP Permissions Configuration
 * No framework dependencies
 */

$permissions = [
    'view_batches' => [
        'title' => 'View Batches',
        'description' => 'Allows viewing batch processing jobs'
    ],
    'create_batches' => [
        'title' => 'Create Batches',
        'description' => 'Allows creating new batch processing jobs'
    ],
    'manage_batches' => [
        'title' => 'Manage Batches',
        'description' => 'Allows managing batch processing jobs'
    ],
    'view_analytics' => [
        'title' => 'View Analytics',
        'description' => 'Allows viewing analytics data'
    ],
    'view_tenant_analytics' => [
        'title' => 'View Tenant Analytics',
        'description' => 'Allows viewing tenant-specific analytics'
    ]
];

return $permissions;
