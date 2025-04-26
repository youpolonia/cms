<?php

return [
    'default_provider' => env('CONTENT_MODERATION_PROVIDER', 'openai'),

    'providers' => [
        'openai' => [
            'service' => \App\Services\OpenAIService::class,
            'method' => 'moderateContent',
            'thresholds' => [
                'hate' => 0.8,
                'hate/threatening' => 0.8,
                'self-harm' => 0.9,
                'sexual' => 0.7,
                'sexual/minors' => 0.8,
                'violence' => 0.8,
                'violence/graphic' => 0.9,
            ],
        ],
        'local' => [
            'service' => \App\Services\LocalModerationService::class,
            'method' => 'check',
            'rules' => [
                'banned_words' => [
                    'words' => [],
                    'action' => 'reject',
                ],
            ],
        ],
    ],

    'routes' => [
        'content*',
        'posts*',
        'comments*',
    ],

    'fields' => [
        'body',
        'content',
        'text',
        'title',
        'description',
    ],

    'priority_rules' => [
        [
            'pattern' => '/\b(sex|porn|nude)\b/i',
            'weight' => 5,
            'flag' => 'explicit_content'
        ],
        [
            'pattern' => '/\b(kill|murder|violence)\b/i',
            'weight' => 4,
            'flag' => 'violent_content'
        ],
        [
            'pattern' => '/\b(hate|racism|sexism)\b/i',
            'weight' => 4,
            'flag' => 'hate_speech'
        ],
        [
            'pattern' => '/\b(spam|advertisement|promo code)\b/i',
            'weight' => 3,
            'flag' => 'spam'
        ],
        [
            'pattern' => '/\b(click here|buy now|limited offer)\b/i',
            'weight' => 2,
            'flag' => 'commercial'
        ]
    ],

    'flag_severity' => [
        'high' => ['explicit_content', 'violent_content', 'hate_speech'],
        'medium' => ['spam', 'commercial'],
        'low' => []
    ],

    'automated_actions' => [
        'high' => ['priority' => 8, 'notify' => true],
        'medium' => ['priority' => 5, 'notify' => false],
        'low' => ['priority' => 0, 'notify' => false]
    ]
];
