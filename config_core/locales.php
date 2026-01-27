<?php

return [
    'default' => 'en',
    'fallback' => 'default', // 'default' or 'none'
    'available' => [
        'en' => 'English',
        'es' => 'Spanish',
        'fr' => 'French',
        'de' => 'German',
        'ar' => 'Arabic',
        'zh' => 'Chinese',
        'ja' => 'Japanese',
        'ru' => 'Russian',
    ],
    'workflow' => [
        'draft',
        'pending_review',
        'approved',
        'published',
        'archived',
    ],
    'rtl' => [
        'ar',
        'he',
        'fa',
        'ur',
    ],
];
