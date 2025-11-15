<?php
/**
 * Export Interface
 * Defines common methods for all export handlers
 */
interface ExportInterface {
    /**
     * Export data in specified format
     * @param array $data Data to export
     * @param array $options Export options
     * @return string Exported data
     */
    public static function export(array $data, array $options = []): string;

    /**
     * Sanitize data row (implemented by each exporter)
     * @param array $row Data row to sanitize
     * @return array Sanitized data
     */
    public static function sanitizeRow(array $row): array;

    /**
     * Check if user has export permission
     * @return bool
     */
    public static function checkExportPermission(): bool;
}
