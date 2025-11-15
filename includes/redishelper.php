<?php

class RedisHelper {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            self::$connection = new Redis();
            self::$connection->connect(
                $_ENV['REDIS_HOST'] ?? '127.0.0.1',
                $_ENV['REDIS_PORT'] ?? 6379
            );
            
            if (!empty($_ENV['REDIS_PASSWORD'])) {
                self::$connection->auth($_ENV['REDIS_PASSWORD']);
            }
        }
        return self::$connection;
    }
}
