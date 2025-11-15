<?php
class JsonImporter {
    /**
     * Parse JSON content into import package
     * @param string $content JSON content
     * @return array Import package structure
     */
    public static function parse(string $content): array {
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON: ' . json_last_error_msg());
        }

        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new Exception('Invalid import format: missing items array');
        }

        return [
            'metadata' => $data['metadata'] ?? [],
            'items' => $data['items'],
            'relationships' => $data['relationships'] ?? []
        ];
    }
}
