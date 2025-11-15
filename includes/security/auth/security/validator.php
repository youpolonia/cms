<?php
namespace Includes\Auth;

class Validator {
    public static function validateUsername(string $username): bool {
        return preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username);
    }

    public static function validatePassword(string $password): bool {
        return strlen($password) >= 8
            && preg_match('/[A-Z]/', $password)
            && preg_match('/[a-z]/', $password)
            && preg_match('/[0-9]/', $password);
    }

    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function validateString(string $input, int $minLength = 1, int $maxLength = 255): bool {
        $length = strlen($input);
        return $length >= $minLength && $length <= $maxLength;
    }

    public static function validateInt($input, int $min = null, int $max = null): bool {
        if (!is_numeric($input)) return false;
        $num = (int)$input;
        return (!isset($min) || $num >= $min) && (!isset($max) || $num <= $max);
    }

    public static function validateFloat($input, float $min = null, float $max = null): bool {
        if (!is_numeric($input)) return false;
        $num = (float)$input;
        return (!isset($min) || $num >= $min) && (!isset($max) || $num <= $max);
    }

    public static function validateBool($input): bool {
        return is_bool($input) || in_array(strtolower($input), ['true', 'false', '1', '0', 'yes', 'no']);
    }

    public static function sanitizeForSQL(string $input): string {
        return addslashes(self::sanitizeInput($input));
    }

    public static function sanitizeHTML(string $input): string {
        return strip_tags(self::sanitizeInput($input));
    }
}
