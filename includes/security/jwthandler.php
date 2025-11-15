<?php
/**
 * JWT Token Handler for CMS
 * Framework-free implementation for PHP 8.1+
 */
class JWTHandler {
    private static string $secretKey;
    private static int $expiryHours = 24;

    public static function init(string $secretKey, int $expiryHours = 24): void {
        self::$secretKey = $secretKey;
        self::$expiryHours = $expiryHours;
    }

    public static function generateToken(array $payload): string {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload['exp'] = time() + (self::$expiryHours * 3600);
        $payloadJson = json_encode($payload);

        $base64Header = self::base64UrlEncode($header);
        $base64Payload = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', "$base64Header.$base64Payload", self::$secretKey, true);
        $base64Signature = self::base64UrlEncode($signature);

        return "$base64Header.$base64Payload.$base64Signature";
    }

    public static function validateToken(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $validSignature = hash_hmac('sha256', "$header.$payload", self::$secretKey, true);
        if (!hash_equals(self::base64UrlDecode($signature), $validSignature)) {
            return null;
        }

        $decodedPayload = json_decode(self::base64UrlDecode($payload), true);
        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            return null;
        }

        return $decodedPayload;
    }

    private static function base64UrlEncode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string {
        $replaced = strtr($data, '-_', '+/');
        $padding = strlen($replaced) % 4;
        if ($padding) {
            $replaced .= str_repeat('=', 4 - $padding);
        }
        return base64_decode($replaced);
    }
}
