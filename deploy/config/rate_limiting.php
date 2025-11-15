<?php
declare(strict_types=1);

return [
    'api' => [
        'max_requests' => 100,
        'window_minutes' => 1,
        'throttle_response' => [
            'status' => 429,
            'body' => [
                'error' => 'TOO_MANY_REQUESTS',
                'message' => 'Rate limit exceeded'
            ]
        ]
    ],
    'federation' => [
        'max_requests' => 30,
        'window_minutes' => 1
    ]
];
