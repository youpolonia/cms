<?php

return [
    'themes' => [
        'active' => env('ACTIVE_THEME', 'default'),
        'path' => resource_path('themes'),
        'public_path' => public_path('themes'),
        'cache' => env('THEME_CACHE', true),
        'config_file' => 'theme.json',
        'required_fields' => [
            'name',
            'version',
            'description'
        ],
        'asset_compilation' => [
            'enabled' => env('THEME_COMPILE_ASSETS', true),
            'driver' => env('THEME_COMPILER', 'vite'),
        ],
    ],

    'theme_assets' => [
        'css' => ['app.css'],
        'js' => ['app.js'],
        'images' => ['**/*.{jpg,jpeg,png,gif,svg,webp}'],
        'fonts' => ['**/*.{woff,woff2,ttf,otf}'],
    ],

    'theme_cache' => [
        'config' => env('THEME_CONFIG_CACHE', true),
        'assets' => env('THEME_ASSETS_CACHE', true),
        'duration' => env('THEME_CACHE_DURATION', 3600),
    ],
];