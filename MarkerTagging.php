<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Marker Tagging System
 * Handles creation, management, and querying of tags for markers
 */
class MarkerTagging
{
    private const TAG_TABLE = 'marker_tags';
    private const TAG_MAPPING_TABLE = 'marker_tag_mappings';

    // ... [previous methods remain unchanged] ...

    /**
     * Create a tag mapping between marker and tag
     */
    private static function createTagMapping(int $markerId, int $tagId): bool
    {
        $db = \core\Database::connection();
        return $db->insert(self::TAG_MAPPING_TABLE, [
            'marker_id' => $markerId,
            'tag_id' => $tagId,
            'created_at' => time()
        ]);
    }

    /**
     * Remove tags from a marker
     * @param int $markerId
     * @param array<int> $tagIds
     * @return int Number of tags removed
     */
    public static function removeTags(int $markerId, array $tagIds): int
    {
        if (empty($tagIds)) {
            return 0;
        }

        $db = \core\Database::connection();
        $result = $db->query(sprintf(
            "DELETE FROM %s WHERE marker_id = %d AND tag_id IN (%s)",
            self::TAG_MAPPING_TABLE,
            $markerId,
            implode(',', array_map('intval', $tagIds))
        ));
        
        return $db->affectedRows();
    }

    /**
     * Get all tags for a specific marker
     * @return array
<array{id: int, name: string}>
     */
    public static
 function getTagsForMarker(int $markerId): array
    {
        $db = \core\Database::connection();
        $query = sprintf(
            "SELECT t.id, t.name FROM %s t 
            JOIN %s m ON t.id = m.tag_id 
            WHERE m.marker_id = %d",
            self::TAG_TABLE,
            self::TAG_MAPPING_TABLE,
            $markerId
        );
        
        $result = $db->query($query);
        $tags = [];
        while ($row = $db->fetchAssoc($result)) {
            $tags[] = [
                'id' => (int)$row['id'],
                'name' => $row['name']
            ];
        }
        return $tags;
    }

    /**
     * Search markers by tags
     * @param array<int> $tagIds
     * @return array<int> Marker IDs that match ALL provided tags
     */
    public static function searchByTags(array $tagIds): array
    {
        if (empty($tagIds)) {
            return [];
        }

        $db = \core\Database::connection();
        $query = sprintf(
            "SELECT marker_id FROM %s 
            WHERE tag_id IN (%s) 
            GROUP BY marker_id 
            HAVING COUNT(DISTINCT tag_id) = %d",
            self::TAG_MAPPING_TABLE,
            implode(',', array_map('intval', $tagIds)),
            count($tagIds)
        );

        $result = $db->query($query);
        $markerIds = [];
        while ($row = $db->fetchAssoc($result)) {
            $markerIds[] = (int)$row['marker_id'];
        }
        return $markerIds;
    }

    /**
     * Get tag statistics (count of markers per tag)
     * @return array
<array{id: int, name: string, count: int}>
     */
    public static
 function getTagStatistics(): array
    {
        $db = \core\Database::connection();
        $query = sprintf(
            "SELECT t.id, t.name, COUNT(m.tag_id) as count 
            FROM %s t LEFT JOIN %s m ON t.id = m.tag_id 
            GROUP BY t.id, t.name",
            self::TAG_TABLE,
            self::TAG_MAPPING_TABLE
        );

        $result = $db->query($query);
        $stats = [];
        while ($row = $db->fetchAssoc($result)) {
            $stats[] = [
                'id' => (int)$row['id'],
                'name' => $row['name'],
                'count' => (int)$row['count']
            ];
        }
        return $stats;
    }
}
