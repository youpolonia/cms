<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ExportValidator
{
    /**
     * Validate export file integrity
     */
    public function validateFileIntegrity(string $filePath): array
    {
        if (!Storage::exists($filePath)) {
            return [
                'valid' => false,
                'errors' => ['File does not exist'],
            ];
        }

        // Basic file validation
        $fileSize = Storage::size($filePath);
        if ($fileSize === 0) {
            return [
                'valid' => false,
                'errors' => ['File is empty'],
            ];
        }

        return [
            'valid' => true,
            'errors' => [],
        ];
    }

    /**
     * Validate export data structure
     */
    public function validateDataStructure(array $data): array
    {
        $errors = [];

        // Check required fields
        $requiredFields = ['id', 'created_at', 'data', 'user_id', 'status'];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data)) {
                $errors[] = "Missing required field: $field";
            }
        }

        // Validate data structure
        if (isset($data['data']) && !is_array($data['data'])) {
            $errors[] = 'Data field must be an array';
        }

        // Validate status values
        $validStatuses = ['processing', 'completed', 'failed', 'archived'];
        if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
            $errors[] = 'Invalid status value';
        }

        // Validate user_id is numeric
        if (isset($data['user_id']) && !is_numeric($data['user_id'])) {
            $errors[] = 'User ID must be numeric';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate data content against schema
     */
    public function validateDataContent(array $data): array
    {
        $errors = [];

        if (isset($data['data'])) {
            // Validate each data item has required fields
            foreach ($data['data'] as $index => $item) {
                if (!isset($item['id'])) {
                    $errors[] = "Item $index missing ID field";
                }
                if (!isset($item['timestamp'])) {
                    $errors[] = "Item $index missing timestamp field";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Validate export against database schema
     */
    public function validateAgainstSchema(array $data): array
    {
        $errors = [];
        $schemaFields = [
            'id' => 'numeric',
            'user_id' => 'numeric',
            'file_path' => 'string',
            'status' => 'string',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime'
        ];

        foreach ($schemaFields as $field => $type) {
            if (isset($data[$field])) {
                $valid = match($type) {
                    'numeric' => is_numeric($data[$field]),
                    'string' => is_string($data[$field]),
                    'datetime' => strtotime($data[$field]) !== false,
                    default => true
                };

                if (!$valid) {
                    $errors[] = "Field $field must be of type $type";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Generate validation report
     */
    public function generateReport(array $validationResults): string
    {
        $report = "Export Validation Report\n";
        $report .= "Generated at: " . Carbon::now()->toDateTimeString() . "\n\n";

        foreach ($validationResults as $check => $result) {
            $report .= "$check: " . ($result['valid'] ? 'PASSED' : 'FAILED') . "\n";
            if (!empty($result['errors'])) {
                $report .= "Errors:\n";
                foreach ($result['errors'] as $error) {
                    $report .= "- $error\n";
                }
            }
            $report .= "\n";
        }

        return $report;
    }
}