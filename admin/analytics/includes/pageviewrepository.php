<?php
/**
 * PageViewRepository - Data access for page_view analytics
 */
class PageViewRepository {
    private static $table = 'page_views';
    
    /**
     * Create a new page view record
     */
    public static function create(array $data): int {
        // Basic validation
        if (empty($data['page_url']) || empty($data['session_id'])) {
            throw new InvalidArgumentException('Missing required fields');
        }

        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'user_id' => null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'referrer' => $_SERVER['HTTP_REFERER'] ?? null
        ];

        $merged = array_merge($defaults, $data);
        
        // Insert into database
        $id = DB::insert(self::$table, $merged);
        return $id;
    }

    /**
     * Get page views by criteria
     */
    public static function getBy(array $criteria, int $limit = 100): array {
        return DB::select(self::$table, $criteria, $limit);
    }

    /**
     * Get aggregated page view counts
     */
    public static function getCounts(string $groupBy = 'page_url', array $filters = []): array {
        $query = "SELECT $groupBy, COUNT(*) as view_count FROM ".self::$table;
        
        if (!empty($filters)) {
            $query .= " WHERE ".implode(' AND ', array_map(fn($k) => "$k = ?", array_keys($filters)));
        }
        
        $query .= " GROUP BY $groupBy ORDER BY view_count DESC";
        
        return DB::query($query, array_values($filters));
    }
}
