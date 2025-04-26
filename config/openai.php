<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API Key and Organization
    |--------------------------------------------------------------------------
    |
    | Here you may specify your OpenAI API Key and organization. This will be
    | used to authenticate with the OpenAI API - you can find your API key
    | and organization on your OpenAI dashboard, at https://openai.com.
    */

    'api_key' => env('OPENAI_API_KEY'),
    'organization' => env('OPENAI_ORGANIZATION'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout may be used to specify the maximum number of seconds to wait
    | for a response. By default, the client will time out after 30 seconds.
    */

    'request_timeout' => env('OPENAI_REQUEST_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Default Model Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default model to use for content generation.
    | Supported models: 'gpt-3.5-turbo', 'gpt-4'
    */
    'default_model' => env('OPENAI_DEFAULT_MODEL', 'gpt-3.5-turbo'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for API requests to prevent excessive usage.
    */
    'rate_limit' => [
        'requests_per_minute' => env('OPENAI_RATE_LIMIT', 60),
        'max_tokens_per_minute' => env('OPENAI_MAX_TOKENS', 90000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Content Generation Parameters
    |--------------------------------------------------------------------------
    |
    | Default parameters for content generation requests.
    */
    'generation' => [
        'max_tokens' => env('OPENAI_MAX_TOKENS', 1000),
        'temperature' => 0.7,
        'presence_penalty' => 0,
        'frequency_penalty' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Configure cost tracking parameters.
    */
    'cost_tracking' => [
        'enabled' => env('OPENAI_COST_TRACKING', true),
        'price_per_token' => [
            'gpt-3.5-turbo' => 0.000002,
            'gpt-4' => 0.00006,
        ],
    ],
];
