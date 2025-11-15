<?php
/**
 * XML Export Handler
 * Implements data export to XML format with memory management and sanitization
 */
class XmlExporter implements ExportInterface {
    /**
     * Export data to XML format
     * @param array $data Data to export
     * @param array $options Export options
     * @return string XML formatted data
     */
    public static function export(array $data, array $options = []): string {
        // Verify user has export permission
        if (!self::hasExportPermission()) {
            throw new Exception('Insufficient permissions for export');
        }

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('data');

        // Process data in chunks for memory efficiency
        $chunkSize = $options['chunk_size'] ?? 1000;
        foreach (array_chunk($data, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $xml->startElement('item');
                foreach (self::sanitizeRow($row) as $key => $value) {
                    $xml->writeElement($key, $value);
                }
                $xml->endElement();
            }
        }

        $xml->endElement();
        return $xml->outputMemory();
    }

    /**
     * Sanitize data row
     * @param array $row Data row to sanitize
     * @return array Sanitized data
     */
    private static function sanitizeRow(array $row): array {
        $sanitized = [];
        foreach ($row as $key => $value) {
            $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1);
        }
        return $sanitized;
    }

    /**
     * Check if user has export permission
     * @return bool
     */
    private static function hasExportPermission(): bool {
        // Implement role-based access control
        return isset($_SESSION['user_role']) && 
               in_array($_SESSION['user_role'], ['admin', 'editor']);
    }
}
