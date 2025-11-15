<?php

// Load environment helper
require_once __DIR__ . '/../../includes/helpers/env.php';

return [
    'content_service' => env('CONTENT_SERVICE_URL', 'http://content-service'),
    'version_service' => env('VERSION_SERVICE_URL', 'http://version-service'),
    'search_service' => env('SEARCH_SERVICE_URL', 'http://search-service'),
    'analytics_service' => env('ANALYTICS_SERVICE_URL', 'http://analytics-service'),
    'moderation_service' => env('MODERATION_SERVICE_URL', 'http://moderation-service')
];
