<?php
class ContentImportService {
    /**
     * Import content from various formats
     * @param string $content File content
     * @param string $format Format (json, xml, csv)
     * @return array Imported content structure
     */
    public static function import(string $content, string $format): array {
        $importer = self::getImporter($format);
        return $importer::parse($content);
    }

    /**
     * Get appropriate importer for format
     * @param string $format
     * @return string Importer class name
     * @throws Exception
     */
    private static function getImporter(string $format): string {
        $importers = [
            'json' => 'JsonImporter',
            'xml' => 'XmlImporter', 
            'csv' => 'CsvImporter'
        ];

        if (!isset($importers[$format])) {
            throw new Exception("Unsupported import format: $format");
        }

        $class = $importers[$format];
        if (!class_exists($class)) {
            throw new Exception("Importer class $class not found");
        }

        return $class;
    }

    /**
     * Validate import package structure
     * @param array $package
     * @throws Exception
     */
    public static function validatePackage(array $package): void {
        if (!isset($package['items']) || !is_array($package['items'])) {
            throw new Exception("Invalid import package: missing items array");
        }

        foreach ($package['items'] as $item) {
            if (!isset($item['type']) || !isset($item['data'])) {
                throw new Exception("Invalid item: missing type or data");
            }
        }
    }
}
