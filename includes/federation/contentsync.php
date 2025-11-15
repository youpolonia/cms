<?php
/**
 * Content Synchronization Service
 */

declare(strict_types=1);

namespace Includes\Federation;

use Includes\Database\Connection;
use Includes\Content\VersionManager;

class ContentSync
{
    private Connection $db;
    private VersionManager $versionManager;

    public function __construct()
    {
        $this->db = new Connection();
        $this->versionManager = new VersionManager();
    }

    /**
     * Push content to federation network
     */
    public function pushContent(array $content): array
    {
        $contentId = $this->db->insert('federation_outgoing', [
            'content_id' => $content['id'],
            'version_hash' => $this->versionManager->getContentHash($content['id']),
            'content_data' => json_encode($content),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return [
            'sync_id' => $contentId,
            'status' => 'queued',
            'timestamp' => time()
        ];
    }

    /**
     * Pull content from federation network
     */
    public function pullContent(array $params): array
    {
        $since = $params['since'] ?? '1970-01-01';
        $limit = min($params['limit'] ?? 100, 500);

        return $this->db->select(
            'federation_incoming',
            ['*'],
            ['created_at >=' => $since],
            ['created_at' => 'DESC'],
            $limit
        );
    }

    /**
     * Resolve content conflicts
     */
    public function resolveConflict(array $conflictData): array
    {
        $resolution = [
            'resolution_type' => $conflictData['resolution_type'],
            'winning_version' => $conflictData['winning_version'],
            'merged_content' => null,
            'resolved_by' => 'system',
            'resolved_at' => date('Y-m-d H:i:s')
        ];

        if ($conflictData['resolution_type'] === 'merge') {
            $resolution['merged_content'] = $this->versionManager->threeWayMerge(
                $conflictData['base_version'],
                $conflictData['local_version'],
                $conflictData['remote_version']
            );
        }

        $this->db->insert('federation_conflicts', $resolution);
        return $resolution;
    }

    /**
     * Verify content signature
     */
    public function verifyContentSignature(string $contentId, string $signature, string $data): bool
    {
        $publicKey = $this->db->selectOne(
            'federation_nodes',
            ['public_key'],
            ['node_id' => $contentId]
        )['public_key'] ?? '';

        return openssl_verify(
            $data,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;
    }
}
