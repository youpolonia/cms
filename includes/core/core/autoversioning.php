<?php
declare(strict_types=1);

require_once __DIR__ . '/VersionManager.php';
require_once __DIR__ . '/contentversionmanager.php';

class AutoVersioning {
    /**
     * Threshold for considering content changed (percentage)
     */
    private const CHANGE_THRESHOLD = 5;

    /**
     * Check if content should be auto-versioned and create version if needed
     * @param int $contentId The content ID
     * @param string $newContent The new content
     * @param string $context Optional context message
     * @return bool Whether a new version was created
     */
    public static function handleContentUpdate(
        int $contentId,
        string $newContent,
        string $context = ''
    ): bool {
        $currentContent = ContentVersionController::getCurrent($contentId);
        if (!$currentContent) {
            return false;
        }

        $changePercentage = self::calculateChangePercentage(
            $currentContent['content'],
            $newContent
        );

        // AI Content Analysis
        $aiAnalysis = AIContentAnalyzer::analyze($newContent);
        if ($aiAnalysis['risk_score'] > 0.7) {
            throw new ContentRiskException('AI detected high-risk content');
        }

        if ($changePercentage >= self::CHANGE_THRESHOLD) {
            return VersionManager::createVersionBeforeUpdate(
                $contentId,
                $currentContent,
                $context ?: "Auto-version: {$changePercentage}% change"
            );
        }

        return false;
    }

    /**
     * Calculate percentage difference between two content strings
     */
    public static function calculateChangePercentage(
        string $oldContent,
        string $newContent
    ): float {
        if ($oldContent === $newContent) {
            return 0;
        }

        $oldHash = ContentVersionManager::createVersionHash($oldContent);
        $newHash = ContentVersionManager::createVersionHash($newContent);

        // Simple similarity comparison using hash segments
        $similarity = 0;
        $segmentLength = 8;
        $totalSegments = strlen($oldHash) / $segmentLength;

        for ($i = 0; $i < $totalSegments; $i++) {
            $oldSegment = substr($oldHash, $i * $segmentLength, $segmentLength);
            $newSegment = substr($newHash, $i * $segmentLength, $segmentLength);
            if ($oldSegment === $newSegment) {
                $similarity++;
            }
        }

        return 100 - ($similarity / $totalSegments * 100);
    }
}
