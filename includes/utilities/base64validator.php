<?php
declare(strict_types=1);

/**
 * Base64 Data Validator
 * Provides comprehensive validation for base64 encoded data
 */
class Base64Validator {
    private const MAX_SIZE = 10485760; // 10MB
    
    /**
     * Validate base64 string format
     */
    public static function isValidFormat(string $data): bool {
        return (bool)preg_match('/^[a-zA-Z0-9\/+]+={0,2}$/', $data);
    }

    /**
     * Validate base64 data size
     */
    public static function isValidSize(string $data): bool {
        $decodedLength = (int)(strlen($data) * 3 / 4);
        return $decodedLength <= self::MAX_SIZE;
    }

    /**
     * Validate base64 image data
     */
    public static function isValidImage(string $data): bool {
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            return false;
        }

        $magicBytes = substr($decoded, 0, 8);
        return strpos($magicBytes, "\x89PNG\x0D\x0A\x1A\x0A") === 0 ||  // PNG
               strpos($magicBytes, "\xFF\xD8\xFF") === 0;               // JPEG
    }

    /**
     * Validate base64 encoded binary data
     */
    public static function isValidBinary(string $data): bool {
        $decoded = base64_decode($data, true);
        return $decoded !== false;
    }

    /**
     * Comprehensive validation for base64 data
     */
    public static function validate(string $data, bool $isImage = false): bool {
        return self::isValidFormat($data) &&
               self::isValidSize($data) &&
               ($isImage ? self::isValidImage($data) : self::isValidBinary($data));
    }
}
