<?php
// Migration functionality has been moved to web-based endpoints
// Please use the admin panel or API endpoints for migrations

http_response_code(403);
header('Content-Type: application/json');
echo json_encode([
    'error' => 'CLI migrations are disabled',
    'message' => 'Please use the web interface at /admin/migrations',
    'documentation' => '/docs/migrations'
]);
exit;
