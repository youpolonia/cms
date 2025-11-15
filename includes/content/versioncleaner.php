<?php

declare(strict_types=1);

namespace Includes\Content;

use PDO;
use PDOException;

class VersionCleaner
{
    private static ?PDO $db = null;
    private static array $defaultRetention = [
        'max_versions' => 5,
        'max_days' => 30
    ];
    private static array $maxLimits = [
        'max_versions' => 20,
        'max_days' => 365
    ];

    public static function initialize(PDO $dbConnection): void
    {
        self::$db = $dbConnection;
    }

    public static function cleanVersions(int $contentId): int
    {
        self::validateConnection();
        $settings = self::getRetentionSettings($contentId);
        
        $query = "DELETE FROM content_versions 
                 WHERE content_id = :content_id
                 AND version_id NOT IN (
                     SELECT version_id FROM content_versions
                     WHERE content_id = :content_id
                     ORDER BY created_at DESC
                     LIMIT :max_versions
                 )
                 AND created_at < datetime('now', '-' || :max_days || ' days')";

        $stmt = self::$db->prepare($query);
        $stmt->bindValue(':content_id', $contentId, PDO::PARAM_INT);
        $stmt->bindValue(':max_versions', $settings['max_versions'], PDO::PARAM_INT);
        $stmt->bindValue(':max_days', $settings['max_days'], PDO::PARAM_INT);
        
        $stmt->execute();
        $deleted = $stmt->rowCount();
        
        if ($deleted > 0) {
            self::logCleanup($contentId, $deleted);
        }
        
        return $deleted;
    }

    private static function getRetentionSettings(int $contentId): array
    {
        self::validateConnection();
        
        try {
            $stmt = self::$db->prepare(
                "SELECT max_versions, max_days
                 FROM content_retention_settings
                 WHERE content_id = :content_id"
            );
            $stmt->bindValue(':content_id', $contentId, PDO::PARAM_INT);
            $stmt->execute();
            
            $settings = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($settings) {
                return self::validateRetention($settings);
            }
        } catch (PDOException $e) {
            Logger::log(
                "VersionCleaner",
                "Failed to fetch retention settings for content ID $contentId: " . $e->getMessage(),
                'error'
            );
        }
        
        return self::$defaultRetention;
    }

    private static function validateRetention(array $settings): array
    {
        $validated = [
            'max_versions' => min(
                (int)($settings['max_versions'] ?? self::$defaultRetention['max_versions']),
                self::$maxLimits['max_versions']
            ),
            'max_days' => min(
                (int)($settings['max_days'] ?? self::$defaultRetention['max_days']),
                self::$maxLimits['max_days']
            )
        ];
        
        return $validated;
    }

    private static function logCleanup(int $contentId, int $deletedCount): void
    {
        Logger::log(
            "VersionCleaner",
            "Cleaned $deletedCount versions for content ID $contentId",
            'info'
        );
    }

    private static function validateConnection(): void
    {
        if (self::$db === null) {
            throw new \RuntimeException('Database connection not initialized');
        }
    }
}
