<?php
/**
 * API endpoint for refreshing worker JWT tokens
 * 
 * This endpoint allows workers to refresh their JWT tokens before they expire
 * 
 * @package CMS
 * @subpackage API
 */

require_once __DIR__ . '/../../includes/coreloader.php';
require_once __DIR__ . '/../../../debug_worker_monitoring_phase5.php';

header('Content-Type: application/json');

try {
    // Log API request for PHASE5-WORKFLOW-STEP4 debugging
    DebugWorkerMonitoringPhase5::logMessage('Token refresh API requested at ' . date('Y-m-d H:i:s'));
    
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('HTTP/1.0 405 Method Not Allowed');
        header('Allow: POST');
        echo json_encode(['error' => 'Method not allowed']);
        exit;
    }
    
    // Get authorization header
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        header('HTTP/1.0 401 Unauthorized');
        echo json_encode(['error' => 'Bearer token required']);
        
        // Log authentication error
        DebugWorkerMonitoringPhase5::logMessage('Missing bearer token in refresh request');
        exit;
    }
    
    $token = $matches[1];
    
    // Get JWT secret
    $jwtSecret = $_ENV['WORKER_JWT_SECRET'] ?? null;
    if (!$jwtSecret) {
        header('HTTP/1.0 500 Internal Server Error');
        echo json_encode(['error' => 'JWT secret not configured']);
        
        // Log error
        DebugWorkerMonitoringPhase5::logError('JWT secret not configured for token refresh');
        exit;
    }
    
    // Validate the token
    try {
        // Parse token parts
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new Exception('Invalid JWT format');
        }
        
        // Verify signature
        $signature = hash_hmac('sha256', "$parts[0].$parts[1]", $jwtSecret, true);
        if (!hash_equals($signature, base64_decode(strtr($parts[2], '-_', '+/')))) {
            throw new Exception('Invalid JWT signature');
        }
        
        // Decode payload
        $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JWT payload');
        }
        
        // Log JWT token details for debugging
        DebugWorkerMonitoringPhase5::logJwtDetails($payload);
        
        // Check if token has expired
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('JWT token has expired');
        }
        
        // Check if worker_id and secret are present
        if (!isset($payload['worker_id']) || !isset($payload['secret'])) {
            throw new Exception('Invalid JWT payload: missing worker_id or secret');
        }
        
        // Validate worker credentials
        $db = \core\Database::connection();
        $stmt = $db->prepare("
            SELECT secret_hash FROM workers
            WHERE id = ? AND is_active = 1
        ");
        $stmt->execute([$payload['worker_id']]);
        $result = $stmt->fetch();
        
        if (!$result || !password_verify($payload['secret'], $result['secret_hash'])) {
            throw new Exception('Invalid worker credentials');
        }
        
        // Generate new token with extended expiration
        $tokenLifetime = defined('WORKER_JWT_LIFETIME') ? WORKER_JWT_LIFETIME : 3600;
        $issuedAt = time();
        $expiresAt = $issuedAt + $tokenLifetime;
        
        $newPayload = [
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'worker_id' => $payload['worker_id'],
            'secret' => $payload['secret']
        ];
        
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT'
        ];
        
        $headerEncoded = rtrim(strtr(base64_encode(json_encode($header)), '+/', '-_'), '=');
        $payloadEncoded = rtrim(strtr(base64_encode(json_encode($newPayload)), '+/', '-_'), '=');
        
        $newSignature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $jwtSecret, true);
        $signatureEncoded = rtrim(strtr(base64_encode($newSignature), '+/', '-_'), '=');
        
        $newToken = "$headerEncoded.$payloadEncoded.$signatureEncoded";
        
        // Log successful token refresh
        DebugWorkerMonitoringPhase5::logMessage('JWT token refreshed for worker ' . $payload['worker_id']);
        
        // Return the new token
        echo json_encode([
            'success' => true,
            'token' => $newToken,
            'expires_at' => date('Y-m-d H:i:s', $expiresAt)
        ]);
    } catch (Exception $e) {
        header('HTTP/1.0 401 Unauthorized');
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Database error']);
        
        // Log error
        DebugWorkerMonitoringPhase5::logError('Token refresh error: ' . $e->getMessage(), $e);
    }
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
    
    // Log error
    DebugWorkerMonitoringPhase5::logError('Token refresh API error: ' . $e->getMessage(), $e);
}
