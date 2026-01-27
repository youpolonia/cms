<?php

require_once __DIR__ . '/../config.php';

class FederationService {
    /**
     * Get federated content for tenant
     * @param string $tenantId
     * @return array
     * @throws Exception
     */
    public static function getContentForTenant(string $tenantId): array {
        if (!self::validateTenant($tenantId)) {
            throw new Exception('Invalid tenant ID', 403);
        }

        try {
            $content = [];
            $content['local'] = self::getLocalContent($tenantId);
            $content['shared'] = self::getSharedContent($tenantId);
            return $content;
        } catch (Exception $e) {
            throw new Exception('Failed to fetch content: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Sync content to federation nodes
     * @param string $contentId
     * @param string $versionHash 
     * @param array $contentData
     * @return bool
     */
    public static function syncContent(string $contentId, string $versionHash, array $contentData): bool {
        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                INSERT INTO federation_outgoing 
                (content_id, version_hash, content_data, created_at)
                VALUES (?, ?, ?, NOW())
            ");
            return $stmt->execute([$contentId, $versionHash, json_encode($contentData)]);
        } catch (Exception $e) {
            error_log("Federation sync failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process incoming federated content
     * @param string $nodeId
     * @param string $contentId
     * @param string $versionHash
     * @param array $contentData
     * @param string $signature
     * @return bool
     */
    public static function processIncomingContent(
        string $nodeId,
        string $contentId, 
        string $versionHash,
        array $contentData,
        string $signature
    ): bool {
        if (!self::verifyNodeSignature($nodeId, $signature)) {
            return false;
        }

        try {
            $db = \core\Database::connection();
            $stmt = $db->prepare("
                INSERT INTO federation_incoming
                (node_id, content_id, version_hash, content_data, signature, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            return $stmt->execute([
                $nodeId,
                $contentId,
                $versionHash,
                json_encode($contentData),
                $signature
            ]);
        } catch (Exception $e) {
            error_log("Incoming content processing failed: " . $e->getMessage());
            return false;
        }
    }

    private static function validateTenant(string $tenantId): bool {
        return !empty($tenantId);
    }

    private static function verifyNodeSignature(string $nodeId, string $signature): bool {
        // TODO: Implement signature verification
        return true;
    }

    private static function getLocalContent(string $tenantId): array {
        return [
            'type' => 'local',
            'tenant' => $tenantId,
            'items' => []
        ];
    }

    private static function getSharedContent(string $tenantId): array {
        return [
            'type' => 'shared', 
            'tenant' => $tenantId,
            'items' => []
        ];
    }
}
