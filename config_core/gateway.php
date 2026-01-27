<?php
/**
 * API Gateway Configuration
 * 
 * Maps external services to internal endpoints with authentication
 */

return [
    'services' => [
        'ai' => [
            'base_url' => 'https://ai-service.internal/api/v1',
            'endpoints' => [
                'generate' => [
                    'path' => '/api/ai/generate',
                    'method' => 'POST',
                    'auth' => [
                        'type' => 'jwt',
                        'required' => true,
                        'scopes' => ['ai.generate']
                    ],
                    'timeout' => 30,
                    'retry' => 2
                ]
            ]
        ]
    ],

    'default_auth' => [
        'jwt' => [
            'secret' => getenv('JWT_SECRET'),
            'issuer' => getenv('APP_URL'),
            'audience' => 'api-gateway'
        ]
    ]
];
