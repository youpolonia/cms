<?php
/**
 * Federation Node Registry
 */

declare(strict_types=1);

namespace Includes\Federation;

use Includes\Database\Connection;

class NodeRegistry
{
    private Connection $db;

    public function __construct()
    {
        $this->db = new Connection();
    }

    /**
     * Register a new federation node
     */
    public function registerNode(array $nodeData): array
    {
        $node = [
            'node_id' => bin2hex(random_bytes(16)),
            'endpoint' => $nodeData['endpoint'],
            'public_key' => $nodeData['public_key'],
            'capabilities' => json_encode($nodeData['capabilities']),
            'last_seen' => date('Y-m-d H:i:s'),
            'status' => 'active'
        ];

        $this->db->insert('federation_nodes', $node);
        return $node;
    }

    /**
     * Get all active federation nodes
     */
    public function getActiveNodes(): array
    {
        return $this->db->select(
            'federation_nodes',
            ['node_id', 'endpoint', 'capabilities', 'last_seen'],
            ['status' => 'active']
        );
    }

    /**
     * Verify node signature
     */
    public function verifyNodeSignature(string $nodeId, string $signature, string $data): bool
    {
        $publicKey = $this->db->selectOne(
            'federation_nodes',
            ['public_key'],
            ['node_id' => $nodeId]
        )['public_key'] ?? '';

        return openssl_verify(
            $data,
            base64_decode($signature),
            $publicKey,
            OPENSSL_ALGO_SHA256
        ) === 1;
    }
}
