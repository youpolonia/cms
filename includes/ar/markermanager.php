<?php
declare(strict_types=1);

require_once __DIR__ . '/../../core/database.php';

class MarkerManager {
    private const METADATA_TABLE = 'marker_metadata';
    private const VERSION_TABLE = 'marker_versions';
    
    private static function createVersion(int $markerId, array $data, string $operation): bool {
        $pdo = \core\Database::connection();
        return $db->insert(self::VERSION_TABLE, [
            'marker_id' => $markerId,
            'version_data' => json_encode($data),
            'operation' => $operation,
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => $_SESSION['user_id'] ?? null
        ]);
    }
    
    public static function getMarkerVersions(int $markerId): array {
        $pdo = \core\Database::connection();
        return $db->selectAll(self::VERSION_TABLE,
            ['id', 'version_data', 'operation', 'created_at', 'user_id'],
            ['marker_id' => $markerId],
            'created_at DESC'
        );
    }

    public static function compareVersions(int $versionId1, int $versionId2): array {
        $pdo = \core\Database::connection();
        
        // Get both versions
        $version1 = $db->selectOne(self::VERSION_TABLE,
            ['version_data', 'operation', 'created_at', 'user_id'],
            ['id' => $versionId1]
        );
        $version2 = $db->selectOne(self::VERSION_TABLE,
            ['version_data', 'operation', 'created_at', 'user_id'],
            ['id' => $versionId2]
        );

        if (!$version1 || !$version2) {
            throw new InvalidArgumentException('One or both versions not found');
        }

        // Decode JSON data
        $data1 = json_decode($version1['version_data'], true);
        $data2 = json_decode($version2['version_data'], true);

        require_once __DIR__ . '/../diffengine.php';
        $diff = DiffEngine::compareJson($data1, $data2);

        return [
            'diff' => $diff,
            'metadata' => [
                'version1' => [
                    'operation' => $version1['operation'],
                    'created_at' => $version1['created_at'],
                    'user_id' => $version1['user_id']
                ],
                'version2' => [
                    'operation' => $version2['operation'],
                    'created_at' => $version2['created_at'],
                    'user_id' => $version2['user_id']
                ]
            ]
        ];
    }
    
    public static function listMarkers(): array {
        // TODO: Implement database query
        // For now return mock data
        return [
            [
                'id' => 1,
                'code' => 'AR123456',
                'created_at' => '2025-05-01 10:00:00',
                'expires_at' => '2026-05-01 10:00:00',
                'status' => 'active'
            ],
            [
                'id' => 2,
                'code' => 'AR789012',
                'created_at' => '2025-05-15 14:30:00',
                'expires_at' => null,
                'status' => 'permanent'
            ]
        ];
    }

    public static function deleteMarker(int $id): bool {
        $marker = self::getMarker($id);
        if ($marker) {
            self::createVersion($id, $marker, 'delete');
        }
        // TODO: Implement actual deletion
        return true;
    }

    public static function generateMarkers(int $count): array {
        $markers = [];
        for ($i = 0; $i < $count; $i++) {
            $marker = [
                'code' => 'AR' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT),
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'active'
            ];
            // TODO: Implement actual database insertion
            $markers[] = $marker;
            self::createVersion($marker['id'] ?? 0, $marker, 'create');
        }
        return $markers;
    }
    
    private static function getMarker(int $id): ?array {
        // TODO: Implement database query
        return null;
    }
    
    public static function setMetadata(int $markerId, string $key, $value): bool {
        $pdo = \core\Database::connection();
        return $db->upsert(self::METADATA_TABLE,
            ['marker_id' => $markerId, 'meta_key' => $key],
            ['meta_value' => is_scalar($value) ? $value : json_encode($value)]
        );
    }
    
    public static function getMetadata(int $markerId, string $key = null) {
        $pdo = \core\Database::connection();
        $where = ['marker_id' => $markerId];
        if ($key !== null) {
            $where['meta_key'] = $key;
        }
        
        $results = $db->selectAll(self::METADATA_TABLE, ['meta_key', 'meta_value'], $where);
        
        if ($key !== null) {
            return $results[0]['meta_value'] ?? null;
        }
        
        $metadata = [];
        foreach ($results as $row) {
            $value = json_decode($row['meta_value'], true) ?? $row['meta_value'];
            $metadata[$row['meta_key']] = $value;
        }
        return $metadata;
    }
    
    public static function deleteMetadata(int $markerId, string $key): bool {
        $pdo = \core\Database::connection();
        return $db->delete(self::METADATA_TABLE, [
            'marker_id' => $markerId,
            'meta_key' => $key
        ]);
    }
}
