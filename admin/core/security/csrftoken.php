<?php
namespace Core\Security;

class CSRFToken {
    public static function generate(): string {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(string $token): bool {
        return isset($_SESSION['csrf_token']) && 
               hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function getInputField(): string {
        return '
<input type="hidden" name="csrf_token" value="' . 
               htmlspecialchars(self::generate(), ENT_QUOTES) . '">';
    }
}
