<?php
require_once __DIR__ . '/exportinterface.php';

/**
 * CSV Exporter - Implements ExportInterface for CSV format exports
 */
class CsvExporter implements ExportInterface
{
    public static function checkExportPermission(): bool
    {
        return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['admin', 'editor']);
    }

    public static function export(array $data, array $options = []): string
    {
        $output = fopen('php://temp', 'w+');
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, self::sanitizeRow($row));
            }
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
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
