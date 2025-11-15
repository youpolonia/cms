<?php
/**
 * GDPR Output Encoding Utilities
 * 
 * Provides safe output encoding for GDPR-related displays
 */

class GDPROutput {
    /**
     * Encode data for HTML output
     */
    public static function html(array $data): string {
        $output = '';
        foreach ($data as $key => $value) {
            $safeKey = htmlspecialchars($key, ENT_QUOTES, 'UTF-8');
            $safeValue = is_array($value) 
                ? self::html($value)
                : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            $output .= "
<div class=\"gdpr-field\"><strong>{
$safeKey}:</strong> {$safeValue}</div>";
        }
        return $output;
    }

    /**
     * Encode for JavaScript/JSON context
     */
    public static function js(array $data): string {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
    }

    /**
     * Format consent dates for display
     */
    public static function formatConsentDate(string $date): string {
        try {
            $dt = new DateTime($date);
            return $dt->format('j M Y, H:i');
        } catch (Exception $e) {
            return 'Invalid date';
        }
    }

    /**
     * Mask sensitive data for logs/display
     */
    public static function maskSensitive(string $data, int $visibleChars = 2): string {
        $length = strlen($data);
        if ($length <= $visibleChars * 2) {
            return str_repeat('*', $length);
        }
        return substr($data, 0, $visibleChars) 
            . str_repeat('*', $length - ($visibleChars * 2))
            . substr($data, -$visibleChars);
    }
}
