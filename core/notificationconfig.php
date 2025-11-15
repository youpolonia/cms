<?php
declare(strict_types=1);

class NotificationConfig {
    private static array $config = [];

    public static function load(string $path): void {
        if (!file_exists($path)) {
            throw new RuntimeException("Config file not found: $path");
        }

        $config = parse_ini_file($path, true);
        if ($config === false) {
            throw new RuntimeException("Failed to parse config file: $path");
        }

        self::$config = $config;
    }

    public static function get(string $key, mixed $default = null): mixed {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }

    public static function init(): void {
        self::load(__DIR__ . '/../config/notifications.ini');
        NotificationService::configure(self::$config);
    }
}
