<?php

return [
    'autosave' => [
        'retention_days' => env('AUTOSAVE_RETENTION_DAYS', 7),
        'keep_min_versions' => env('AUTOSAVE_KEEP_MIN_VERSIONS', 3),
    ],
    
    'manual' => [
        'retention_days' => env('MANUAL_VERSION_RETENTION_DAYS', 30),
    ],
];