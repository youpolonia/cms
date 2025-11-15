<?php
namespace Includes\Auth;

use Exception;
use Firebase\JWT\JWT as FirebaseJWT;
use Firebase\JWT\Key;

class JWT {
    private static $secretKey;
    private static $algorithm = 'HS256';
    private static $expiration = 3600; // 1 hour
    private static $issuer = '';
    private static $audience = '';

    public static function init() {
        if (empty(self::$secretKey)) {
            self::$secretKey = bin2hex(random_bytes(32));
        }
    }

    public static function configure(string $issuer, string $audience): void {
        self::$issuer = $issuer;
        self::$audience = $audience;
    }

    public static function generateToken(array $payload): string {
        self::init();
        $issuedAt = time();
        $payload = array_merge([
            'iat' => $issuedAt,
            'exp' => $issuedAt + self::$expiration,
            'jti' => bin2hex(random_bytes(16)),
            'iss' => self::$issuer,
            'aud' => self::$audience
        ], $payload);

        return FirebaseJWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    public static function validateToken(string $token): array {
        self::init();
        try {
            $decoded = FirebaseJWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            $payload = (array)$decoded;
            
            self::validateExpiration($payload);
            self::validateIssuer($payload);
            self::validateAudience($payload);
            
            return $payload;
        } catch (Exception $e) {
            throw new JWTValidationException('JWT validation failed: ' . $e->getMessage());
        }
    }

    private static function validateExpiration(array $payload): void {
        if (!isset($payload['exp']) || $payload['exp'] < time()) {
            throw new JWTExpiredException('Token has expired');
        }
    }

    private static function validateIssuer(array $payload): void {
        if (self::$issuer && (!isset($payload['iss']) || $payload['iss'] !== self::$issuer)) {
            throw new JWTValidationException('Invalid token issuer');
        }
    }

    private static function validateAudience(array $payload): void {
        if (self::$audience && (!isset($payload['aud']) || $payload['aud'] !== self::$audience)) {
            throw new JWTValidationException('Invalid token audience');
        }
    }

    public static function getSecretKey(): string {
        self::init();
        return self::$secretKey;
    }
}

class JWTValidationException extends Exception {}
class JWTExpiredException extends JWTValidationException {}
