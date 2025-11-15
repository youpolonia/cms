<?php
namespace Includes\Auth;

class CSRFToken {
    private static $tokenName = 'csrf_token';

    public static function generate(): string {
        if (empty($_SESSION[self::$tokenName])) {
            $_SESSION[self::$tokenName] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$tokenName];
    }

    public static function validate(string $token): bool {
        if (empty($_SESSION[self::$tokenName])) {
            return false;
        }
        return hash_equals($_SESSION[self::$tokenName], $token);
    }

    public static function getTokenName(): string {
        return self::$tokenName;
    }
}
