<?php
/**
 * JWTHandler - Framework-free JWT implementation
 *
 * Features:
 * - HMAC-based signing/verification
 * - exp/iat validation
 * - jti generation
 * - Detailed error reporting
 * - Security logging
 */

require_once __DIR__ . '/securitylogger.php';

class JWTHandler {
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $logFile = __DIR__ . '/../../logs/security.log';

    /**
     * Initialize with secret key
     */
    public static function init(string $secretKey): void {
        self::$secretKey = $secretKey;
    }

    /**
     * Generate a new JWT
     */
    public static function generateToken(array $payload, int $expiry = 3600): string {
        if (empty(self::$secretKey)) {
            throw new Exception('JWTHandler not initialized - call init() first');
        }

        // Set standard claims
        $payload['iat'] = time();
        $payload['exp'] = time() + $expiry;
        $payload['jti'] = bin2hex(random_bytes(16));

        // Create token parts
        $header = self::base64UrlEncode(json_encode([
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ]));

        $payloadJson = json_encode($payload);
        $payloadEncoded = self::base64UrlEncode($payloadJson);
        $signature = self::base64UrlEncode(self::createSignature($header . '.' . $payloadEncoded));

        $token = $header . '.' . $payloadEncoded . '.' . $signature;

        SecurityLogger::log('JWT_GENERATED', 'Token generated', ['payload_keys' => array_keys((array)$payload)]);

        return $token;
    }

    /**
     * Validate JWT token
     */
    public static function isTokenValid(string $token): array {
        try {
            if (empty(self::$secretKey)) {
                throw new Exception('JWTHandler not initialized - call init() first');
            }

            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new Exception('Invalid token format');
            }

            list($header, $payload, $signature) = $parts;

            // Verify signature
            $expectedSignature = self::createSignature($header . '.' . $payload);
            if (!hash_equals(self::base64UrlDecode($signature), $expectedSignature)) {
                throw new Exception('Invalid signature');
            }

            // Decode payload
            $decodedPayload = json_decode(self::base64UrlDecode($payload), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid payload encoding');
            }

            // Validate timestamps
            $now = time();
            if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < $now) {
                throw new Exception('Token expired');
            }
            if (isset($decodedPayload['iat']) && $decodedPayload['iat'] > $now) {
                throw new Exception('Token issued in future');
            }

            return [
                'valid' => true,
                'payload' => $decodedPayload
            ];
        } catch (Exception $e) {
            self::logError($e->getMessage(), $token);
            return [
                'valid' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create HMAC signature
     */
    private static function createSignature(string $data): string {
        return hash_hmac('sha256', $data, self::$secretKey, true);
    }

    /**
     * Base64 URL encoding
     */
    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decoding
     */
    private static function base64UrlDecode(string $data): string {
        $replaced = strtr($data, '-_', '+/');
        $padding = strlen($replaced) % 4;
        if ($padding) {
            $replaced .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($replaced);
    }

    /**
     * Log security errors using SecurityLogger
     */
    private static function logError(string $error, string $token = ''): void {
        $eventType = strpos($error, 'Invalid') !== false ? 'JWT_VALIDATION_ERROR' : 'JWT_SECURITY_ERROR';
        $message = "JWT Error: $error";
        $context = ['token' => substr($token, 0, 50) . (strlen($token) > 50 ? '...' : '')];
        
        SecurityLogger::log($eventType, $message, $context);
    }
}
