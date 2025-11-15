<?php

namespace Core;

class Helper
{
    /**
     * Sanitize user input to prevent XSS and other attacks
     * 
     * @param string $input The input to sanitize
     * @param bool $stripTags Whether to strip HTML tags
     * @return string Sanitized input
     */
    public static function sanitizeInput(string $input, bool $stripTags = true): string
    {
        if ($stripTags) {
            $input = strip_tags($input);
        }
        
        return htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Convert a string to a URL-friendly slug
     * 
     * @param string $text The text to convert
     * @param string $divider The word separator
     * @return string The generated slug
     */
    public static function generateSlug(string $text, string $divider = '-'): string
    {
        // Replace non-letter or digits with divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        
        // Transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        
        // Remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);
        
        // Trim and lowercase
        $text = trim($text, $divider);
        $text = strtolower($text);
        
        return $text ?: 'n-a';
    }

    /**
     * Format a date in a consistent way
     * 
     * @param string|int $date The date (timestamp or string)
     * @param string $format The output format (default: Y-m-d H:i:s)
     * @return string Formatted date
     */
    public static function formatDate($date, string $format = 'Y-m-d H:i:s'): string
    {
        if (is_numeric($date)) {
            return date($format, (int)$date);
        }
        
        $timestamp = strtotime($date);
        return $timestamp ? date($format, $timestamp) : '';
    }

    /**
     * Truncate text to specified length with ellipsis
     * 
     * @param string $text The text to truncate
     * @param int $length Max length before truncation
     * @param string $ellipsis The ellipsis string
     * @return string Truncated text
     */
    public static function truncateText(string $text, int $length = 100, string $ellipsis = '...'): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        return mb_substr($text, 0, $length) . $ellipsis;
    }

    /**
     * Validate an email address
     * 
     * @param string $email The email to validate
     * @return bool Whether the email is valid
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Generate a secure random token
     * 
     * @param int $length Token length in bytes
     * @return string The generated token
     */
    public static function generateToken(int $length = 32): string
    {
        try {
            return bin2hex(random_bytes($length));
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not generate random token: ' . $e->getMessage());
        }
    }

    /**
     * Get file extension from filename
     * 
     * @param string $filename The filename
     * @return string The file extension (lowercase)
     */
    public static function getFileExtension(string $filename): string
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Convert array to object
     * 
     * @param array $array The array to convert
     * @return \stdClass The resulting object
     */
    public static function arrayToObject(array $array): \stdClass
    {
        $object = new \stdClass();
        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? self::arrayToObject($value) : $value;
        }
        return $object;
    }

    /**
     * Convert object to array
     * 
     * @param object $object The object to convert
     * @return array The resulting array
     */
    public static function objectToArray(object $object): array
    {
        $array = [];
        foreach (get_object_vars($object) as $key => $value) {
            $array[$key] = is_object($value) ? self::objectToArray($value) : $value;
        }
        return $array;
    }
}
