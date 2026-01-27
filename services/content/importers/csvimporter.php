<?php
class CsvImporter {
    /**
     * Parse CSV content into import package
     * @param string $content CSV content
     * @return array Import package structure
     */
    public static function parse(string $content): array {
        $lines = array_filter(explode("\n", $content));
        if (count($lines) < 2) {
            throw new Exception('CSV must have at least header and one data row');
        }

        $headers = str_getcsv(array_shift($lines));
        $items = [];
        
        foreach ($lines as $line) {
            $values = str_getcsv($line);
            if (count($values) !== count($headers)) {
                continue; // Skip malformed rows
            }
            $items[] = array_combine($headers, $values);
        }

        return [
            'metadata' => ['format' => 'csv'],
            'items' => $items,
            'relationships' => []
        ];
    }
}
