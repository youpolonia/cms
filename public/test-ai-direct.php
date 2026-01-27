<?php
header('Content-Type: application/json');
define('CMS_ROOT', dirname(__DIR__));

\ = CMS_ROOT . '/config/ai_settings.json';
\ = json_decode(file_get_contents(\), true);
\ = \['default_provider'] ?? 'openai';
\ = \['providers'][\] ?? null;

\ = [
    'model' => \['default_model'] ?? 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'You are a professional website copywriter. Output ONLY the requested content with no explanations.'],
        ['role' => 'user', 'content' => 'Generate a compelling heading (max 10 words) for a website section about: inspiration and motivation. Return only the heading text.']
    ],
    'temperature' => 0.7,
    'max_tokens' => 100
];

\ = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array(\, [
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . \['api_key']],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode(\),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);

\ = curl_exec(\);
\ = curl_getinfo(\, CURLINFO_HTTP_CODE);
\ = curl_error(\);
curl_close(\);

echo json_encode([
    'http_code' => \,
    'curl_error' => \,
    'response' => json_decode(\, true)
], JSON_PRETTY_PRINT);
