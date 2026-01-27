<?php

class VersionCleaner {
    private static $config;

    public static function init() {
        self::$config = require __DIR__ . '/../config/versions.php';
    }

    public static function cleanupVersions($contentId, $retentionCount = null, $retentionDays = null) {
        if (!self::$config) {
            self::init();
        }
        $retentionCount = $retentionCount ?? self::$config['retention']['default_count'];
        $retentionDays = $retentionDays ?? self::$config['retention']['default_days'];
        
        $versions = self::getContentVersions($contentId);
        $versionsToKeep = self::filterVersionsToKeep($versions, $retentionCount, $retentionDays);
        $versionsToDelete = array_diff($versions, $versionsToKeep);
        
        foreach ($versionsToDelete as $version) {
            self::deleteVersion($contentId, $version);
        }
        
        return count($versionsToDelete);
    }

    private static function getContentVersions($contentId) {
        // Implementation depends on version storage system
        // Returns array of version strings sorted by creation date (newest first)
        return [];
    }

    public static function filterVersionsToKeep(array $versions, int $retentionCount, int $retentionDays): array {
        $cutoffDate = time() - ($retentionDays * 24 * 60 * 60);
        $versionsToKeep = array_slice($versions, 0, $retentionCount);
        
        foreach ($versions as $version) {
            $versionDate = self::getVersionDate($version);
            if ($versionDate > $cutoffDate && !in_array($version, $versionsToKeep)) {
                $versionsToKeep[] = $version;
            }
        }
        
        return array_unique($versionsToKeep);
    }

    private static function deleteVersion($contentId, $version) {
        // Implementation depends on version storage system
        Logger::log("Deleted version $version of content $contentId");
    }

    private static function getVersionDate($version) {
        // Implementation depends on version storage system
        return strtotime($version);
    }

    public static function getCleanupStats() {
        return [
            'default_retention_count' => self::$defaultRetentionCount,
            'default_retention_days' => self::$defaultRetentionDays,
            'last_run' => date('Y-m-d H:i:s')
        ];
    }
}
