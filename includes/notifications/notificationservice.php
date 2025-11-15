<?php
/**
 * NotificationService - Framework-free notification service
 * Handles email, SMS, and in-app notifications
 */
class NotificationService {
    private static $emailConfig;
    private static $smsConfig;
    private static $dbConnection;

    /**
     * Initialize service with configuration
     * @param array $emailConfig SMTP configuration
     * @param array $smsConfig SMS gateway configuration 
     * @param mixed $dbConnection Database connection
     */
    public static function init(array $emailConfig, array $smsConfig, $dbConnection): void {
        self::$emailConfig = $emailConfig;
        self::$smsConfig = $smsConfig;
        self::$dbConnection = $dbConnection;
    }

    /**
     * Send email notification
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email content
     * @return bool True if sent successfully
     */
    public static function sendEmail(string $to, string $subject, string $body): bool {
        // Validate email config
        if (empty(self::$emailConfig)) {
            error_log('Email configuration not initialized');
            return false;
        }

        // Mock implementation - would use PHPMailer or similar in production
        $headers = "From: " . self::$emailConfig['from'] . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

        return mail($to, $subject, $body, $headers);
    }

    /**
     * Send SMS notification
     * @param string $phone Recipient phone number
     * @param string $message SMS content
     * @return bool True if sent successfully
     */
    public static function sendSMS(string $phone, string $message): bool {
        // Validate SMS config
        if (empty(self::$smsConfig)) {
            error_log('SMS configuration not initialized');
            return false;
        }

        // Mock implementation - would use Twilio or similar in production
        $url = self::$smsConfig['gateway_url'] . '?phone=' . urlencode($phone) . '&message=' . urlencode($message);
        $response = file_get_contents($url);
        
        return strpos($response, 'success') !== false;
    }

    /**
     * Log notification in database
     * @param int $userId Recipient user ID
     * @param string $type Notification type
     * @param string $message Notification content
     * @return bool True if logged successfully
     */
    public static function logNotification(int $userId, string $type, string $message): bool {
        if (!self::$dbConnection) {
            error_log('Database connection not initialized');
            return false;
        }

        // Mock implementation - would use prepared statements in production
        $query = "INSERT INTO notifications (user_id, type, message, is_read, created_at) 
                  VALUES ($userId, '$type', '$message', 0, NOW())";
        
        return self::$dbConnection->query($query) !== false;
    }

    /**
     * Get unread notifications for user
     * @param int $userId
     * @return array List of notifications
     */
    public static function getUnreadNotifications(int $userId): array {
        if (!self::$dbConnection) {
            error_log('Database connection not initialized');
            return [];
        }

        // Mock implementation - would use prepared statements in production
        $query = "SELECT * FROM notifications
                 WHERE user_id = $userId AND is_read = 0
                 ORDER BY created_at DESC";
        $result = self::$dbConnection->query($query);

        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}
