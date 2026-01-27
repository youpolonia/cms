<?php
/**
 * API Documentation Endpoint
 */

$documentation = [
    'name' => 'CMS Public API',
    'version' => API_VERSION,
    'base_url' => '/api/v2',
    'authentication' => [
        'type' => 'Bearer Token / API Key',
        'header' => 'Authorization: Bearer YOUR_API_KEY',
        'alternative' => 'X-API-Key: YOUR_API_KEY',
    ],
    'rate_limit' => [
        'requests' => 100,
        'window' => '1 minute',
    ],
    'endpoints' => [
        'status' => [
            'GET /status' => 'API health check (public)',
        ],
        'version' => [
            'GET /version' => 'API version info (public)',
        ],
        'pages' => [
            'GET /pages' => 'List all pages',
            'GET /pages/{id}' => 'Get single page',
            'POST /pages' => 'Create new page',
            'PUT /pages/{id}' => 'Update page',
            'DELETE /pages/{id}' => 'Delete page',
        ],
        'posts' => [
            'GET /posts' => 'List all posts',
            'GET /posts/{id}' => 'Get single post',
            'POST /posts' => 'Create new post',
            'PUT /posts/{id}' => 'Update post',
            'DELETE /posts/{id}' => 'Delete post',
        ],
        'media' => [
            'GET /media' => 'List media files',
            'GET /media/{id}' => 'Get media details',
            'POST /media' => 'Upload media (multipart/form-data)',
            'DELETE /media/{id}' => 'Delete media',
        ],
        'users' => [
            'GET /users' => 'List users (admin only)',
            'GET /users/{id}' => 'Get user details',
            'POST /users' => 'Create user (admin only)',
            'PUT /users/{id}' => 'Update user',
            'DELETE /users/{id}' => 'Delete user (admin only)',
        ],
        'webhooks' => [
            'GET /webhooks' => 'List webhooks',
            'POST /webhooks' => 'Create webhook',
            'PUT /webhooks/{id}' => 'Update webhook',
            'DELETE /webhooks/{id}' => 'Delete webhook',
            'POST /webhooks/{id}/test' => 'Test webhook',
        ],
        'ai' => [
            'POST /ai/generate' => 'Generate content with AI',
            'POST /ai/rewrite' => 'Rewrite content',
            'POST /ai/seo-analyze' => 'Analyze page SEO',
        ],
    ],
    'errors' => [
        '400' => 'Bad Request - Invalid parameters',
        '401' => 'Unauthorized - Missing or invalid API key',
        '403' => 'Forbidden - Insufficient permissions',
        '404' => 'Not Found - Resource does not exist',
        '429' => 'Too Many Requests - Rate limit exceeded',
        '500' => 'Internal Server Error',
    ],
    'response_format' => [
        'success' => [
            'ok' => true,
            'status' => 200,
            'data' => '...',
            'timestamp' => '2024-01-01T00:00:00Z',
        ],
        'error' => [
            'ok' => false,
            'status' => 400,
            'error' => [
                'message' => 'Error description',
                'code' => 'ERROR_CODE',
            ],
            'timestamp' => '2024-01-01T00:00:00Z',
        ],
    ],
];

api_response($documentation);
