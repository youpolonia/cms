<?php
declare(strict_types=1);

class ValidationHelper {
    /**
     * Validate required fields exist in array
     * @param array $data Input data
     * @param array $requiredFields List of required field names
     * @throws InvalidArgumentException If any required field is missing
     */
    public static function validateRequiredFields(array $data, array $requiredFields): void {
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }
    }

    /**
     * Validate value is integer
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @throws InvalidArgumentException If value is not integer
     */
    public static function validateInteger($value, string $fieldName): void {
        if (!is_int($value)) {
            throw new InvalidArgumentException("Field $fieldName must be integer");
        }
    }

    /**
     * Validate workflow type
     * @param string $type Workflow type to validate
     * @throws InvalidArgumentException If invalid workflow type
     */
    public static function validateWorkflowType(string $type): void {
        $validTypes = ['default', 'expedited', 'review'];
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException("Invalid workflow type: $type");
        }
    }

    /**
     * Validate content share payload structure
     * @param array $payload Payload to validate
     * @return bool True if valid
     */
    public static function validateContentSharePayload(array $payload): bool {
        $required = ['content_id', 'target_tenants', 'version'];
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate string value
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @param int $maxLength Maximum allowed length
     * @throws InvalidArgumentException If invalid string
     */
    public static function validateString($value, string $fieldName, int $maxLength = 255): void {
        if (!is_string($value)) {
            throw new InvalidArgumentException("Field $fieldName must be string");
        }
        if (strlen($value) > $maxLength) {
            throw new InvalidArgumentException("Field $fieldName exceeds maximum length of $maxLength");
        }
    }

    /**
     * Validate boolean value
     * @param mixed $value Value to validate
     * @param string $fieldName Field name for error message
     * @throws InvalidArgumentException If invalid boolean
     */
    public static function validateBoolean($value, string $fieldName): void {
        if (!is_bool($value)) {
            throw new InvalidArgumentException("Field $fieldName must be boolean");
        }
    }

    /**
     * Validate date string format (YYYY-MM-DD)
     * @param string $date Date string to validate
     * @param string $fieldName Field name for error message
     * @throws InvalidArgumentException If invalid date format
     */
    public static function validateDate(string $date, string $fieldName): void {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException("Field $fieldName must be in YYYY-MM-DD format");
        }
    }

    /**
     * Validate email format
     * @param string $email Email to validate
     * @param string $fieldName Field name for error message
     * @throws InvalidArgumentException If invalid email
     */
    public static function validateEmail(string $email, string $fieldName): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Field $fieldName must be valid email");
        }
    }
}
