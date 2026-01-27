<?php
class XmlImporter {
    /**
     * Parse XML content into import package
     * @param string $content XML content
     * @return array Import package structure
     */
    public static function parse(string $content): array {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new Exception('Invalid XML: ' . $errors[0]->message);
        }

        $result = [
            'metadata' => [],
            'items' => [],
            'relationships' => []
        ];

        // Parse metadata if exists
        if (isset($xml->metadata)) {
            $result['metadata'] = [
                'created' => (string)($xml->metadata->created ?? date('c')),
                'system_version' => (string)($xml->metadata->system_version ?? '1.0'),
                'content_count' => (int)($xml->metadata->content_count ?? 0)
            ];
        }

        // Parse items if exists
        if (isset($xml->items)) {
            foreach ($xml->items->item as $item) {
                $result['items'][] = [
                    'id' => (string)($item->id ?? ''),
                    'type' => (string)($item->type ?? 'content'),
                    'data' => isset($item->data) ? (array)$item->data : [],
                    'meta' => [
                        'created' => (string)($item->meta->created ?? date('c')),
                        'modified' => (string)($item->meta->modified ?? date('c')),
                        'author' => (int)($item->meta->author ?? 0)
                    ]
                ];
            }
        }

        return $result;
    }
}
