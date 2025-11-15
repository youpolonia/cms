<?php
/**
 * CMS Input Sanitization Utilities
 * Framework-free implementation for PHP 8.1+
 *
 * @package CMS\Core
 */

class Sanitization {
    // String sanitization
    public static function filterString($input, int $maxLength = 255): ?string {
        if (!is_scalar($input)) return null;
        $filtered = trim((string)$input);
        return empty($filtered) ? null : 
            mb_substr(htmlspecialchars($filtered, ENT_QUOTES, 'UTF-8'), 0, $maxLength);
    }

    // Integer sanitization
    public static function filterInt($input, ?int $min = null, ?int $max = null): ?int {
        if (!is_numeric($input)) return null;
        $value = (int)$input;
        return (($min !== null && $value < $min) || ($max !== null && $value > $max)) ? null : $value;
    }

    // SQL escaping
    public static function escapeSql(string $input): string {
        return str_replace(
            ['\\', '\'', '"', "\0", "\n", "\r", "\x1a"],
            ['\\\\', '\\\'', '\\"', '\\0', '\\n', '\\r', '\\Z'],
            $input
        );
    }

    // Email validation
    public static function filterEmail($input): ?string {
        if (!is_scalar($input)) return null;
        $email = trim((string)$input);
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? strtolower($email) : null;
    }

    // HTML sanitization
    public static function filterHtml(string $input, array $allowedTags = ['p','br','strong','em','ul','ol','li']): string {
        $allowed = '';
        foreach ($allowedTags as $tag) $allowed .= "<$tag>";
        return htmlspecialchars(strip_tags($input, $allowed), ENT_NOQUOTES, 'UTF-8');
    }

    // JSON sanitization
    public static function sanitizeJsonInput(string $json): string {
        $cleaned = mb_convert_encoding($json, 'UTF-8', 'UTF-8');
        if (preg_match('/[\x{D800}-\x{DFFF}]/u', $cleaned)) {
            $cleaned = preg_replace('/[\x{D800}-\x{DFFF}]/u', "\xEF\xBF\xBD", $cleaned);
        }
        return $cleaned;
    }

    // Safe JSON encoding
    public static function safeJsonEncode($data, int $options = 0, int $depth = 512): string {
        $options |= JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR;
        $json = json_encode($data, $options, $depth);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new JsonException(json_last_error_msg(), json_last_error());
        }
        return $json;
    }

    // Safe JSON decoding
    public static function safeJsonDecode(string $json, bool $assoc = false, int $depth = 512, int $options = 0) {
        $sanitized = self::sanitizeJsonInput($json);
        $options |= JSON_THROW_ON_ERROR;
        return json_decode($sanitized, $assoc, $depth, $options);
    }

    // Error logging
    public static function logJsonError(Exception $e, string $context = ''): void {
        $message = sprintf('[JSON Error] %s: %s in %s:%d', $context, $e->getMessage(), $e->getFile(), $e->getLine());
        error_log($message);
        if (is_writable(__DIR__ . '/../logs/json_errors.log')) {
            file_put_contents(__DIR__ . '/../logs/json_errors.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
        }
    }
}
