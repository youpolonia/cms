<?php
class CSRF {
    private static $tokenName = 'csrf_token';
    private static $tokenLength = 32;

    public static function generateToken(): string {
        if (empty($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(self::$tokenLength));
        }
        return $_SESSION[self::$tokenName];
    }

    public static function validateToken(string $token): bool {
        if (empty($_SESSION[self::$tokenName])) {
            return false;
        }
        return hash_equals($_SESSION[self::$tokenName], $token);
    }

    public static function getTokenName(): string {
        return self::$tokenName;
    }
}
