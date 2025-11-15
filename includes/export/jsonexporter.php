<?php
require_once __DIR__ . '/exportinterface.php';

/**
 * JSON Exporter - Implements ExportInterface for JSON format exports
 */
class JsonExporter implements ExportInterface
{
    public static function checkExportPermission(): bool
    {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']);
    }

    public static function export(array $data, array $options = []): string
    {
        $sanitizedData = array_map([self::class, 'sanitizeRow'], $data);
        return json_encode($sanitizedData, JSON_PRETTY_PRINT);
    }

    public static function sanitizeRow(array $row): array
    {
        $sanitized = [];
        foreach ($row as $key => $value) {
            $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
        return $sanitized;
    }
}
