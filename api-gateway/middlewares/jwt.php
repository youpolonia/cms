<?php
/**
 * JWT Utility for token generation and validation
 */
class JWT {
    private static $secret;
    private static $algorithm = 'HS256';
    private static $expiry = 3600; // 1 hour
    
    public static function init(string $secret): void {
        self::$secret = $secret;
    }

    public static function generate(array $payload): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => self::$algorithm]);
        $payload['exp'] = time() + self::$expiry;
        $payload = json_encode($payload);
        
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$secret, true);
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validate(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        
        [$header, $payload, $signature] = $parts;
        
        $validSignature = hash_hmac('sha256', "$header.$payload", self::$secret, true);
        $base64ValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));
        
        if (!hash_equals($base64ValidSignature, $signature)) {
            return null;
        }
        
        $decodedPayload = json_decode(base64_decode($payload), true);
        if (!$decodedPayload || !isset($decodedPayload['exp']) || $decodedPayload['exp'] < time()) {
            return null;
        }
        
        return $decodedPayload;
    }
}
