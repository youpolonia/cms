<?php
declare(strict_types=1);

class ValidationHelper {
    public static function validateRequiredFields(array $data, array $requiredFields): void {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }
    }

    public static function validateInteger(mixed $value, string $fieldName): void {
        if (!is_numeric($value) || (int)$value != $value) {
            throw new InvalidArgumentException("$fieldName must be an integer");
        }
    }

    public static function validateWorkflowType(string $type): void {
        $validTypes = ['default', 'urgent', 'review'];
        if (!in_array($type, $validTypes, true)) {
            throw new InvalidArgumentException(
                "Invalid workflow type. Must be one of: " . implode(', ', $validTypes)
            );
        }
    }
}
