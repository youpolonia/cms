<?php

class TenantValidator {
    /**
     * Validates tenant ID according to RFC 4122 UUID format
     * @param string $id Tenant ID to validate
     * @return string Sanitized tenant ID
     * @throws InvalidTenantIdException If validation fails
     */
    public static function validateId(string $id): string {
        $id = self::sanitize($id);
        
        if (!self::isValidUuid($id)) {
            throw new InvalidTenantIdException('Invalid tenant identifier format');
        }
        
        return $id;
    }

    /**
     * Sanitizes tenant ID (trim, lowercase)
     * @param string $id Raw tenant ID
     * @return string Sanitized tenant ID
     */
    public static function sanitize(string $id): string {
        $id = trim($id);
        $id = strtolower($id);
        return $id;
    }

    /**
     * Checks if string is valid RFC 4122 UUID
     * @param string $uuid String to check
     * @return bool True if valid UUID
     */
    private static function isValidUuid(string $uuid): bool {
        $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
        return preg_match($pattern, $uuid) === 1;
    }
}

class InvalidTenantIdException extends InvalidArgumentException {
    public function __construct($message = "Invalid tenant identifier", $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
