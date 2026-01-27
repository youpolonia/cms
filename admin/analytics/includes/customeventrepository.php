<?php
/**
 * CustomEventRepository - Data access for custom analytics events
 */
class CustomEventRepository {
    private static $table = 'custom_events';
    
    /**
     * Create a new custom event record
     */
    public static function create(array $data): int {
        // Basic validation
        if (empty($data['event_name'])) {
            throw new InvalidArgumentException('Event name is required');
        }

        $defaults = [
            'created_at' => date('Y-m-d H:i:s'),
            'session_id' => $data['session_id'] ?? '',
            'user_id' => null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            'event_data' => json_encode($data['event_data'] ?? []),
            'page_url' => $data['page_url'] ?? ''
        ];

        $merged = array_merge($defaults, $data);
        
        // Insert into database
        $id = DB::insert(self::$table, $merged);
        return $id;
    }

    /**
     * Get custom events by criteria
     */
    public static function getBy(array $criteria, int $limit = 100): array {
        return DB::select(self::$table, $criteria, $limit);
    }

    /**
     * Get event counts by name
     */
    public static function getEventCounts(): array {
        $query = "SELECT event_name, COUNT(*) as count 
                 FROM ".self::$table." 
                 GROUP BY event_name 
                 ORDER BY count DESC";
        
        return DB::query($query);
    }
}
