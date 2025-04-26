<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Active Theme
    |--------------------------------------------------------------------------
    |
    | Specifies the currently active theme that should be used. This can be
    | changed at runtime to switch themes.
    |
    */
    'active' => env('ACTIVE_THEME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Theme Path
    |--------------------------------------------------------------------------
    |
    | The base path where themes are located. Each theme should be in its own
    | subdirectory.
    |
    */
    'path' => resource_path('themes'),

    /*
    |--------------------------------------------------------------------------
    | Theme Cache
    |--------------------------------------------------------------------------
    |
    | Determines whether theme assets should be cached for better performance.
    |
    */
    'cache' => env('THEME_CACHE', true),

    /*
    |--------------------------------------------------------------------------
    | Fallback Theme
    |--------------------------------------------------------------------------
    |
    | Specifies the theme to use when the active theme is not available.
    |
    */
    'fallback' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Theme Structure
    |--------------------------------------------------------------------------
    |
    | Defines the standard directory structure for themes.
    |
    */
    'structure' => [
        'assets' => [
            'css' => 'assets/css',
            'js' => 'assets/js',
            'images' => 'assets/images'
        ],
        'views' => 'templates',
        'partials' => 'partials'
    ]
];