<?php

class AnalyticsService {
    private static $dbConnection;

    public static function initialize($connection) {
        self::$dbConnection = $connection;
    }

    public static function trackEvent($eventName, $eventData = []) {
        if (!self::$dbConnection) {
            throw new Exception("Database connection not initialized");
        }

        $query = "INSERT INTO ANALYTICS_METRICS (event_name, event_data, created_at) 
                  VALUES (?, ?, NOW())";
        
        $stmt = self::$dbConnection->prepare($query);
        $jsonData = json_encode($eventData);
        
        return $stmt->execute([$eventName, $jsonData]);
    }

    public static function getEvents($limit = 100) {
        if (!self::$dbConnection) {
            throw new Exception("Database connection not initialized");
        }

        $query = "SELECT * FROM ANALYTICS_METRICS ORDER BY created_at DESC LIMIT ?";
        $stmt = self::$dbConnection->prepare($query);
        $stmt->execute([$limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getEventCount($eventName) {
        if (!self::$dbConnection) {
            throw new Exception("Database connection not initialized");
        }

        $query = "SELECT COUNT(*) as count FROM ANALYTICS_METRICS WHERE event_name = ?";
        $stmt = self::$dbConnection->prepare($query);
        $stmt->execute([$eventName]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }
}
