<?php
declare(strict_types=1);

/**
 * Version Synchronizer - Handles content versioning and conflict resolution
 */
final class VersionSynchronizer
{
    private static array $versions = [];

    public static function trackVersion(
        string $contentId,
        string $content,
        string $author,
        int $timestamp
    ): string {
        $versionId = self::generateVersionId($contentId);
        
        self::$versions[$contentId][$versionId] = [
            'content' => $content,
            'author' => $author,
            'timestamp' => $timestamp,
            'parent' => self::getLatestVersionId($contentId)
        ];

        return $versionId;
    }

    public static function getVersion(string $contentId, string $versionId): ?array
    {
        return self::$versions[$contentId][$versionId] ?? null;
    }

    public static function detectConflicts(string $contentId): array
    {
        $versions = self::$versions[$contentId] ?? [];
        if (count($versions) < 2) {
            return [];
        }

        $latest = self::getLatestVersionId($contentId);
        $conflicts = [];

        foreach ($versions as $id => $version) {
            if ($id !== $latest && $version['parent'] === $versions[$latest]['parent']) {
                $conflicts[] = $id;
            }
        }

        return $conflicts;
    }

    private static function generateVersionId(string $contentId): string
    {
        return $contentId . '-' . substr(md5(microtime()), 0, 8);
    }

    private static function getLatestVersionId(string $contentId): ?string
    {
        if (empty(self::$versions[$contentId])) {
            return null;
        }

        $latest = null;
        foreach (self::$versions[$contentId] as $id => $version) {
            if ($latest === null || $version['timestamp'] > self::$versions[$contentId][$latest]['timestamp']) {
                $latest = $id;
            }
        }

        return $latest;
    }
}
