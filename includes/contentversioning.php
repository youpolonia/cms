<?php
declare(strict_types=1);

class ContentVersioning {
    private static string $storagePath = '/var/www/html/cms/storage/versions';

    public static function createVersion(int $contentId, string $content): string {
        $versionId = uniqid('v_');
        $filePath = self::$storagePath . "/{$contentId}_{$versionId}.json";
        
        if (!file_put_contents($filePath, json_encode([
            'content' => $content,
            'created_at' => time(),
            'version_id' => $versionId
        ]))) {
            throw new RuntimeException("Failed to create version for content {$contentId}");
        }

        return $versionId;
    }

    public static function getVersion(int $contentId, string $versionId): array {
        // Try new storage path first
        $filePath = self::$storagePath . "/{$contentId}_{$versionId}.json";
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true);
        }

        // Fall back to old storage method
        $filePath = self::getVersionFilePath($contentId, $versionId);
        if (!file_exists($filePath)) {
            throw new RuntimeException('Version file not found');
        }

        $content = file_get_contents($filePath);
        if ($content === false) {
            throw new RuntimeException('Failed to read version file');
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid version data format');
        }

        return $data;
    }

    public static function listVersions(int $contentId): array {
        $pattern = self::$storagePath . "/{$contentId}_v_*.json";
        return array_map(fn($f) => basename($f), glob($pattern));
    }

    public static function diffVersions(int $contentId, string $version1, string $version2): array {
        $v1 = self::getVersion($contentId, $version1);
        $v2 = self::getVersion($contentId, $version2);

        return [
            'changes' => self::calculateDiff($v1['content'], $v2['content']),
            'version1' => $version1,
            'version2' => $version2,
            'content_id' => $contentId
        ];
    }

    private static function validateVersionOwnership(int $contentId, string $versionId): bool {
        $versionDir = self::getVersionDirectory($contentId);
        $versionFile = "$versionDir/$versionId.json";
        return file_exists($versionFile);
    }

    private static function getVersionDirectory(int $contentId): string {
        $baseDir = __DIR__ . '/../content_versions';
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        return "$baseDir/$contentId";
    }

    private static function getVersionFilePath(int $contentId, string $versionId): string {
        $versionDir = self::getVersionDirectory($contentId);
        if (!file_exists($versionDir)) {
            mkdir($versionDir, 0755, true);
        }
        return "$versionDir/$versionId.json";
    }

    private static function calculateDiff(string $old, string $new): array {
        $oldLines = explode("\n", $old);
        $newLines = explode("\n", $new);
        $diff = [];

        foreach ($newLines as $i => $line) {
            if (!isset($oldLines[$i])) {
                $diff[] = ['type' => 'added', 'line' => $i+1, 'content' => $line];
            } elseif ($line !== $oldLines[$i]) {
                $diff[] = [
                    'type' => 'modified',
                    'line' => $i+1,
                    'old' => $oldLines[$i],
                    'new' => $line
                ];
            }
        }

        return $diff;
    }
}
