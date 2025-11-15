<?php
/**
 * Input validation helper
 */
class InputValidator {
    public static function sanitizeString(string $input): string {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public static function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateInt(string $input, int $min = null, int $max = null): bool {
        $options = [];
        if ($min !== null) $options['min_range'] = $min;
        if ($max !== null) $options['max_range'] = $max;
        
        return filter_var($input, FILTER_VALIDATE_INT, ['options' => $options]) !== false;
    }

    public static function validateFloat(string $input, float $min = null, float $max = null): bool {
        $options = [];
        if ($min !== null) $options['min_range'] = $min;
        if ($max !== null) $options['max_range'] = $max;
        
        return filter_var($input, FILTER_VALIDATE_FLOAT, ['options' => $options]) !== false;
    }

    public static function validateUrl(string $url): bool {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function validateRegex(string $input, string $pattern): bool {
        return preg_match($pattern, $input) === 1;
    }
}
