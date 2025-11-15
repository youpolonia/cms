<?php
/**
 * AI Services Configuration
 * 
 * Note: Store actual API keys in environment-specific config files
 * that are excluded from version control
 */

return [
    'description_service' => [
        'api_endpoint' => 'https://api.openai.com/v1/completions',
        'api_key' => '', // To be set in environment config
        'default_model' => 'text-davinci-003',
        'max_tokens' => 200,
        'temperature' => 0.7
    ]
];
