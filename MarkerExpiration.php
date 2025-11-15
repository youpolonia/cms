<?php
declare(strict_types=1);

/**
 * Marker Expiration System
 * Handles expiration tracking and notifications for markers
 */
class MarkerExpiration
{
    private const MARKER_TABLE = 'markers';
    private const NOTIFICATION_TABLE = 'expiration_notifications';

    // ... [previous methods remain unchanged] ...

    /**
     * Get marker data for notification
     */
    private static function getMarkerData(int $markerId): ?array
    {
        $db = \core\Database::connection();
        $result = $db->query(sprintf(
            "SELECT id, user_id, title, expiration_date 
            FROM %s WHERE id = %d",
            self::MARKER_TABLE,
            $markerId
        ));
        
        return $db->fetchAssoc($result) ?: null;
    }

    /**
     * Process expired markers (run via cron)
     * @return array<int> IDs of processed markers
     */
    public static function processExpiredMarkers(): array
    {
        $db = \core\Database::connection();
        $currentTime = time();
        
        // Get expired markers
        $query = sprintf(
            "SELECT id FROM %s 
            WHERE expiration_date > 0 
            AND expiration_date <= %d 
            AND expired = 0",
            self::MARKER_TABLE,
            $currentTime
        );
        
        $result = $db->query($query);
        $processedMarkers = [];
        
        while ($row = $db->fetchAssoc($result)) {
            $markerId = (int)$row['id'];
            if (self::handleMarkerExpiration($markerId)) {
                $processedMarkers[] = $markerId;
            }
        }
        
        return $processedMarkers;
    }

    /**
     * Handle individual marker expiration
     */
    private static function handleMarkerExpiration(int $markerId): bool
    {
        $db = \core\Database::connection();
        
        try {
            $db->beginTransaction();
            
            // Mark as expired
            $db->update(
                self::MARKER_TABLE,
                ['expired' => 1],
                ['id' => $markerId]
            );
            
            // Archive marker data
            MarkerArchiver::archive($markerId);
            
            // Send final expiration notification
            NotificationSystem::create(
                'marker_expired',
                self::getMarkerOwner($markerId),
                ['marker_id' => $markerId]
            );
            
            return $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            ErrorLogger::log($e);
            return false;
        }
    }

    /**
     * Get marker owner ID
     */
    private static function getMarkerOwner(int $markerId): int
    {
        $db = \core\Database::connection();
        $result = $db->query(sprintf(
            "SELECT user_id FROM %s WHERE id = %d",
            self::MARKER_TABLE,
            $markerId
        ));
        $row = $db->fetchAssoc($result);
        return (int)$row['user_id'];
    }
}
