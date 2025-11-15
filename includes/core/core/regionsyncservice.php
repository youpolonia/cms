<?php
declare(strict_types=1);

class RegionSyncService {
    private static array $regions = [];
    private static string $currentRegion;

    public static function init(string $currentRegion): void {
        self::$currentRegion = $currentRegion;
    }

    public static function addRegion(string $regionId, string $endpoint): void {
        self::$regions[$regionId] = [
            'endpoint' => $endpoint,
            'last_sync' => null,
            'status' => 'active'
        ];
    }

    public static function syncContent(string $contentId, array $targetRegions): array {
        $results = [];
        $content = self::getLocalContent($contentId);
        
        foreach ($targetRegions as $region) {
            if (!isset(self::$regions[$region])) {
                continue;
            }

            $results[$region] = self::sendToRegion(
                $region,
                $content,
                self::$regions[$region]['endpoint']
            );
        }

        return $results;
    }

    private static function getLocalContent(string $contentId): array {
        // Implementation depends on your content storage
        return [
            'id' => $contentId,
            'content' => '', // Actual content here
            'hash' => self::generateContentHash($contentId)
        ];
    }

    private static function sendToRegion(string $region, array $content, string $endpoint): array {
        // REST API implementation for content sync
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Region: ' . self::$currentRegion
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $status === 200 ? 'success' : 'failed',
            'response' => json_decode($response, true) ?? []
        ];
    }

    public static function generateContentHash(string $contentId): string {
        return hash('sha256', $contentId . time());
    }
}
