<?php
declare(strict_types=1);

/**
 * Marker Expiration Scheduler
 * Handles scheduled execution of expiration checks
 */
class MarkerExpirationScheduler
{
    /**
     * Run daily expiration checks
     */
    public static function runDailyChecks(): void
    {
        // Check for markers expiring soon
        $notified = MarkerExpiration::checkExpiringMarkers();
        
        // Process fully expired markers
        $processed = MarkerExpiration::processExpiredMarkers();
        
        // Log results
        if (!empty($notified) || !empty($processed)) {
            $logMessage = sprintf(
                "Marker expiration checks completed. Notified: %d, Processed: %d",
                count($notified),
                count($processed)
            );
            SystemLogger::log('marker_expiration', $logMessage);
        }
    }

    /**
     * Get scheduler configuration
     */
    public static function getSchedule(): array
    {
        return [
            'task' => 'marker_expiration_check',
            'frequency' => 'daily',
            'callback' => [self::class, 'runDailyChecks'],
            'description' => 'Checks for expiring markers and sends notifications'
        ];
    }
}
