<?php

return [
    'framework' => [
        'legacy' => 'laravel',
        'current' => 'custom',
        'transition_complete' => true,
        'completed_at' => '2025-05-10',
    ],
    'themes' => [
        'active' => getenv('ACTIVE_THEME') ?: 'default',
        'path' => \CMS_ROOT . '/assets/themes',
        'public_path' => \CMS_ROOT . '/public/themes',
        'cache' => getenv('THEME_CACHE') !== false ? (bool)getenv('THEME_CACHE') : true,
        'config_file' => 'theme.json',
        'required_fields' => [
            'name',
            'version',
            'description'
        ],
        'asset_compilation' => [
            'enabled' => getenv('THEME_COMPILE_ASSETS') !== false ? (bool)getenv('THEME_COMPILE_ASSETS') : true,
            'driver' => getenv('THEME_COMPILER') ?: 'vite',
        ],
    ],

    'theme_assets' => [
        'css' => ['app.css'],
        'js' => ['app.js'],
        'images' => ['**/*.{jpg,jpeg,png,gif,svg,webp}'],
        'fonts' => ['**/*.{woff,woff2,ttf,otf}'],
    ],

    'theme_cache' => [
        'config' => getenv('THEME_CONFIG_CACHE') !== false ? (bool)getenv('THEME_CONFIG_CACHE') : true,
        'assets' => getenv('THEME_ASSETS_CACHE') !== false ? (bool)getenv('THEME_ASSETS_CACHE') : true,
        'duration' => getenv('THEME_CACHE_DURATION') !== false ? (int)getenv('THEME_CACHE_DURATION') : 3600,
    ],
];
