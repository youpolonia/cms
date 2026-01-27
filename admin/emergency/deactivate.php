<?php
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../../core/logger/EmergencyLogger.php';

// Validate API key
$validApiKey = 'EMERGENCY_DEACTIVATE_KEY'; // Should be configured in secure config
if (!isset($_POST['api_key']) || $_POST['api_key'] !== $validApiKey) {
    csrf_validate_or_403();
    http_response_code(401);
    exit('Invalid API key');
}

// Rate limiting
$ip = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = __DIR__ . '/../../logs/rate-limit-deactivate-' . preg_replace('/[^a-f0-9.:]/', '', $ip) . '.log';

// Check rate limit (max 5 attempts per minute)
if (file_exists($rateLimitFile)) {
    $attempts = explode("\n", trim(file_get_contents($rateLimitFile)));
    $attempts = array_filter($attempts);
    $lastMinuteAttempts = array_filter($attempts, fn($t) => time() - (int)$t <= 60);
    
    if (count($lastMinuteAttempts) >= 5) {
        EmergencyLogger::log('Rate limit exceeded', $ip);
        http_response_code(429);
        exit('Too many requests');
    }
}

// Log attempt
file_put_contents($rateLimitFile, time() . "\n", FILE_APPEND);
EmergencyLogger::log('Deactivation attempt', $ip);

// Main deactivation logic
// ... (implementation specific to your CMS)

http_response_code(200);
echo 'Emergency mode deactivated';
