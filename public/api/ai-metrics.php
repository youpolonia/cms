<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../includes/admin-auth.php';

// Check admin permissions
if (!AdminAuth::hasPermission('view_analytics')) {
    http_response_code(403);
    die(json_encode(['error' => 'Access denied']));
}

// Simulate AI metrics data (in a real implementation, this would query a database)
$metrics = [
    'today' => rand(50, 200),
    'week' => rand(300, 1000),
    'month' => rand(1000, 5000),
    'models' => [
        'text-generation' => rand(100, 500),
        'image-generation' => rand(50, 300),
        'code-assistance' => rand(20, 150)
    ],
    'timestamps' => [
        'last_updated' => date('Y-m-d H:i:s')
    ]
];

echo json_encode($metrics);
