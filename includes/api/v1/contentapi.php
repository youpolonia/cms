<?php
declare(strict_types=1);

require_once __DIR__ . '/../../Controllers/ContentVersionController.php';

class ContentApi {
    /**
     * Gets list of versions for content
     */
    public static function listVersions(int $contentId): array {
        $versions = ContentVersionController::list($contentId);
        return ['success' => true, 'data' => $versions];
    }

    /**
     * Gets diff between two versions
     */
    public static function compareVersions(
        int $contentId,
        int $version1,
        int $version2
    ): array {
        $v1 = ContentVersionController::get($contentId, $version1);
        $v2 = ContentVersionController::get($contentId, $version2);

        if (!$v1 || !$v2) {
            return ['success' => false, 'error' => 'Version not found'];
        }

        return [
            'success' => true,
            'data' => [
                'from' => $v1,
                'to' => $v2,
                'diff' => self::calculateDiff(
                    json_decode($v1['data'], true),
                    json_decode($v2['data'], true)
                )
            ]
        ];
    }

    /**
     * Calculates diff between two content versions
     */
    private static function calculateDiff(array $old, array $new): array {
        $diff = [];
        
        foreach ($old as $key => $value) {
            if (!array_key_exists($key, $new)) {
                $diff[$key] = ['removed' => $value];
            } elseif ($new[$key] !== $value) {
                $diff[$key] = [
                    'old' => $value,
                    'new' => $new[$key]
                ];
            }
        }

        foreach ($new as $key => $value) {
            if (!array_key_exists($key, $old)) {
                $diff[$key] = ['added' => $value];
            }
        }

        return $diff;
    }
}
