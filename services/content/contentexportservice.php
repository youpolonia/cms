<?php
/**
 * Content Export Service
 * Handles bulk content exports with versioning and relationships
 */
class ContentExportService {
    const EXPORT_DIR = __DIR__ . '/../../exports/';
    const MAX_EXPORT_SIZE = 10485760; // 10MB

    /**
     * Export content items to specified format
     * @param array $contentIds Array of content IDs to export
     * @param string $format Export format (json|csv|xml)
     * @return string Path to exported file
     */
    public static function exportContent(array $contentIds, string $format = 'json'): string {
        if (!self::validatePermissions()) {
            throw new Exception('Export permission denied');
        }

        $package = self::createExportPackage($contentIds);
        $filename = self::generateFilename($format);

        switch ($format) {
            case 'json':
                $output = JsonExporter::export($package);
                break;
            case 'csv':
                $output = CsvExporter::export($package);
                break;
            case 'xml':
                $output = XmlExporter::export($package);
                break;
            default:
                throw new Exception("Unsupported export format: $format");
        }

        if (strlen($output) > self::MAX_EXPORT_SIZE) {
            throw new Exception('Export size exceeds maximum limit');
        }

        file_put_contents(self::EXPORT_DIR . $filename, $output);
        return $filename;
    }

    private static function createExportPackage(array $contentIds): array {
        $package = [
            'metadata' => [
                'created' => date('c'),
                'system_version' => CMS_VERSION,
                'content_count' => count($contentIds)
            ],
            'items' => [],
            'relationships' => []
        ];

        foreach ($contentIds as $id) {
            $contentModel = new ContentModel();
            $content = $contentModel->getById($id);
            if ($content) {
                $package['items'][] = self::processContentItem($content);
                $package['relationships'] = array_merge(
                    $package['relationships'],
                    RelationshipModel::getContentRelationships($id)
                );
            }
        }

        return $package;
    }

    private static function processContentItem(array $content): array {
        return [
            'id' => $content['id'],
            'type' => $content['type'],
            'data' => $content['data'],
            'versions' => VersionModel::getContentVersions($content['id']),
            'meta' => [
                'created' => $content['created_at'],
                'modified' => $content['updated_at'],
                'author' => $content['author_id']
            ]
        ];
    }

    private static function validatePermissions(): bool {
        return AuthService::hasPermission('content_export');
    }

    private static function generateFilename(string $format): string {
        return 'export_' . date('Ymd_His') . '.' . $format;
    }
}
