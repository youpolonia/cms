<?php
declare(strict_types=1);

require_once __DIR__ . '/regionsyncservice.php';

class RegionSyncController {
    public static function handleSyncRequest(array $request): array {
        if (!self::validateRequest($request)) {
            return ['status' => 'error', 'message' => 'Invalid request'];
        }

        $contentId = $request['content_id'];
        $sourceRegion = $request['source_region'];
        $contentHash = $request['content_hash'];

        if (!self::verifyContentHash($contentId, $contentHash)) {
            return ['status' => 'error', 'message' => 'Hash mismatch'];
        }

        $result = ContentStorage::storeContent(
            $contentId,
            $request['content'],
            $sourceRegion
        );

        return [
            'status' => $result ? 'success' : 'error',
            'content_id' => $contentId,
            'stored_at' => time()
        ];
    }

    public static function handleHealthCheck(): array {
        return [
            'status' => 'healthy',
            'region' => RegionSyncService::getCurrentRegion(),
            'timestamp' => time(),
            'services' => [
                'database' => true,
                'storage' => true,
                'cache' => true
            ]
        ];
    }

    private static function validateRequest(array $request): bool {
        return isset(
            $request['content_id'],
            $request['content'],
            $request['source_region'],
            $request['content_hash']
        );
    }

    private static function verifyContentHash(string $contentId, string $hash): bool {
        $expected = RegionSyncService::generateContentHash($contentId);
        return hash_equals($expected, $hash);
    }
}
