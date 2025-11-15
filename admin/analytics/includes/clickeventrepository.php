<?php
/**
 * ClickEventRepository - Data access for click_event analytics
 */
class ClickEventRepository {
    private static $table = 'click_events';
    
    /**
     * Create a new click event record
     */
    public static function create(array $data): int {
        // Basic validation
        if (empty($data['element_id']) || empty($data['page_url'])) {
            throw new InvalidArgumentException('Missing required fields');
        }

        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'session_id' => $data['session_id'] ?? '',
            'user_id' => null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'element_text' => $data['element_text'] ?? '',
            'coordinates_x' => $data['coordinates_x'] ?? null,
            'coordinates_y' => $data['coordinates_y'] ?? null
        ];

        $merged = array_merge($defaults, $data);
        
        // Insert into database
        $id = DB::insert(self::$table, $merged);
        return $id;
    }

    /**
     * Get click events by criteria
     */
    public static function getBy(array $criteria, int $limit = 100): array {
        return DB::select(self::$table, $criteria, $limit);
    }

    /**
     * Get heatmap data for a page
     */
    public static function getHeatmapData(string $pageUrl): array {
        $query = "SELECT coordinates_x, coordinates_y, COUNT(*) as click_count 
                 FROM ".self::$table." 
                 WHERE page_url = ? 
                 GROUP BY coordinates_x, coordinates_y";
        
        return DB::query($query, [$pageUrl]);
    }
}
