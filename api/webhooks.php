<?php
// Secure Webhook Receiver
// Implements HMAC verification, timestamp validation, replay protection

// Configuration
define('WEBHOOK_SECRET', 'your_shared_secret_here'); // Set in config
define('TIMESTAMP_WINDOW', 300); // 5 minutes in seconds
define('MAX_RETRIES', 3);

// Track processed nonces to prevent replay attacks
$processedNonces = [];

// Get request payload
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Validate required headers
if (!isset($headers['X-Signature']) || 
    !isset($headers['X-Timestamp']) || 
    !isset($headers['X-Nonce'])) {
    http_response_code(400);
    exit('Missing required headers');
}

// Verify HMAC signature
$computedSignature = hash_hmac('sha256', $headers['X-Timestamp'].$headers['X-Nonce'].$payload, WEBHOOK_SECRET);
if (!hash_equals($computedSignature, $headers['X-Signature'])) {
    http_response_code(401);
    exit('Invalid signature');
}

// Validate timestamp (5 minute window)
$currentTime = time();
if (abs($currentTime - (int)$headers['X-Timestamp']) > TIMESTAMP_WINDOW) {
    http_response_code(400);
    exit('Timestamp outside allowed window');
}

// Check for replay attacks
if (in_array($headers['X-Nonce'], $processedNonces)) {
    http_response_code(409);
    exit('Nonce already processed');
}

// Store nonce (in production use persistent storage)
$processedNonces[] = $headers['X-Nonce'];

// Process payload
try {
    $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
    
    // Implement retry logic
    $retryCount = (int)($headers['X-Retry-Count'] ?? 0);
    if ($retryCount > MAX_RETRIES) {
        http_response_code(429);
        exit('Max retries exceeded');
    }
    
    // Process webhook data here
    // ...
    
    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(500);
    error_log('Webhook processing error: ' . $e->getMessage());
    exit('Error processing webhook');
}
