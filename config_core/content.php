<?php
return [
    'home_page' => [
        'source' => 'database', // 'database' or 'static'
        'page_id' => 'home', // ID of the home page in content_pages table
        'fallback_enabled' => true,
        'fallback_template' => 'home/fallback'
    ],
    'markdown' => [
        'auto_links' => true,
        'auto_paragraphs' => true,
        'html_allowed' => false,
        'safe_mode' => true
    ]
];
