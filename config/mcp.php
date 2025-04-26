<?php

return [
    'api_key' => env('MCP_API_KEY'),
    
    'servers' => [
        'knowledge' => [
            'base_uri' => env('MCP_KNOWLEDGE_URL', 'https://api.mcp.example.com/v1/knowledge'),
            'timeout' => 30,
        ],
        'content' => [
            'base_uri' => env('MCP_CONTENT_URL', 'https://api.mcp.example.com/v1/content'),
            'timeout' => 60,
        ],
        'summarization' => [
            'base_uri' => env('MCP_SUMMARY_URL', 'https://api.mcp.example.com/v1/summary'),
            'timeout' => 45,
        ],
        'seo' => [
            'base_uri' => env('MCP_SEO_URL', 'https://api.mcp.example.com/v1/seo'),
            'timeout' => 45,
        ],
        'media' => [
            'base_uri' => env('MCP_MEDIA_URL', 'https://api.mcp.example.com/v1/media'),
            'timeout' => 60,
        ],
    ],

    'cache' => [
        'content_ttl' => env('MCP_CONTENT_CACHE_TTL', 3600),
        'summary_ttl' => env('MCP_SUMMARY_CACHE_TTL', 3600),
        'seo_ttl' => env('MCP_SEO_CACHE_TTL', 3600),
    ],
    
    'content_generation' => [
        'url' => env('MCP_CONTENT_GENERATION_URL', 'http://localhost:8080/generate/content'),
        'cache_ttl' => env('MCP_CONTENT_GENERATION_CACHE_TTL', 3600),
        'content_types' => [
            'blog_post' => [
                'min_tokens' => 500,
                'max_tokens' => 2000,
                'default_tone' => 'informative'
            ],
            'product_description' => [
                'min_tokens' => 100,
                'max_tokens' => 500,
                'default_tone' => 'persuasive'
            ],
            'faq' => [
                'min_tokens' => 50,
                'max_tokens' => 300,
                'default_tone' => 'friendly'
            ],
            'news_article' => [
                'min_tokens' => 300,
                'max_tokens' => 1500,
                'default_tone' => 'neutral'
            ]
        ],
        'tones' => ['informative', 'persuasive', 'friendly', 'neutral', 'professional', 'casual'],
        'styles' => ['concise', 'detailed', 'technical', 'creative']
    ],
];