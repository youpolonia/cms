<?php

declare(strict_types=1);

namespace Utils;

class CSRFToken
{
    private const TOKEN_LIFETIME = 3600; // 1 hour

    public static function generate(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_expires'] = time() + self::TOKEN_LIFETIME;
        return $token;
    }

    public static function validate(string $token): bool
    {
        if (!isset($_SESSION['csrf_token'], $_SESSION['csrf_token_expires'])) {
            return false;
        }

        if (time() > $_SESSION['csrf_token_expires']) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_expires']);
            return false;
        }

        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function getToken(): ?string
    {
        return $_SESSION['csrf_token'] ?? null;
    }
}
