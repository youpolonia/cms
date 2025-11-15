<?php

namespace Core;

use Includes\Database\Database;
use InvalidArgumentException;

class CustomFieldManager {
    /**
     * Get all custom fields for a content type
     * @param string $contentType The content type identifier
     * @return array Array of field definitions
     * @throws InvalidArgumentException If content type is empty
     */
    public static function getFieldsForType(string $contentType): array {
        if (empty($contentType)) {
            throw new InvalidArgumentException('Content type cannot be empty');
        }

        $fields = Database::query(
            "SELECT * FROM content_type_fields 
             WHERE content_type = :content_type 
             ORDER BY field_order ASC",
            ['content_type' => $contentType]
        );

        // Decode JSON options if present
        return array_map(function($field) {
            if (!empty($field['options'])) {
                $field['options'] = json_decode($field['options'], true);
            }
            return $field;
        }, $fields);
    }

    /**
     * Get field values for specific content
     * @param int $contentId The content ID
     * @return array Associative array of field values [field_name => value]
     * @throws InvalidArgumentException If content ID is invalid
     */
    public static function getFieldValues(int $contentId): array {
        if ($contentId <= 0) {
            throw new InvalidArgumentException('Invalid content ID');
        }

        $values = Database::query(
            "SELECT field_name, field_value FROM content_field_values 
             WHERE content_id = :content_id",
            ['content_id' => $contentId]
        );

        $result = [];
        foreach ($values as $value) {
            $result[$value['field_name']] = $value['field_value'];
        }
        return $result;
    }

    /**
     * Save field values for content
     * @param int $contentId The content ID
     * @param array $data Associative array of field values [field_name => value]
     * @return bool True on success
     * @throws InvalidArgumentException If content ID or data is invalid
     */
    public static function saveFieldValues(int $contentId, array $data): bool {
        if ($contentId <= 0) {
            throw new InvalidArgumentException('Invalid content ID');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('No field data provided');
        }

        // Begin transaction
        try {
            // First delete existing values
            Database::execute(
                "DELETE FROM content_field_values WHERE content_id = :content_id",
                ['content_id' => $contentId]
            );

            // Insert new values
            foreach ($data as $fieldName => $fieldValue) {
                if (!is_string($fieldName) || empty($fieldName)) {
                    continue; // Skip invalid field names
                }

                Database::execute(
                    "INSERT INTO content_field_values 
                     (content_id, field_name, field_value) 
                     VALUES (:content_id, :field_name, :field_value)",
                    [
                        'content_id' => $contentId,
                        'field_name' => $fieldName,
                        'field_value' => is_array($fieldValue) ? json_encode($fieldValue) : $fieldValue
                    ]
                );
            }

            return true;
        } catch (\Exception $e) {
            throw new \RuntimeException("Failed to save field values: " . $e->getMessage());
        }
    }
}
