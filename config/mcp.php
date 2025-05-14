<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the Model Context Protocol (MCP) servers
    | for various services including AI content generation and personalization.
    |
    */

    'base_url' => env('MCP_BASE_URL', 'http://localhost:8080'),
    'api_key' => env('MCP_API_KEY'),
    'timeout' => env('MCP_TIMEOUT', 30),
    'retry_times' => env('MCP_RETRY_TIMES', 3),
    'retry_sleep' => env('MCP_RETRY_SLEEP', 100),

    /*
    |--------------------------------------------------------------------------
    | Personalization Engine Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for connecting to the personalization engine MCP server.
    |
    */
    'personalization' => [
        'base_url' => env('MCP_PERSONALIZATION_BASE_URL', 'http://localhost:8081'),
        'api_key' => env('MCP_PERSONALIZATION_API_KEY', 'default_personalization_key'),
        'timeout' => env('MCP_PERSONALIZATION_TIMEOUT', 30),
    ],
];