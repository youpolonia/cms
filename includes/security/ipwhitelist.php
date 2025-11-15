<?php
declare(strict_types=1);

class IPWhitelist {
    private static $whitelist = [];
    private const CONFIG_FILE = __DIR__ . '/../../config/ip_whitelist.php';

    public static function init(): void {
        if (file_exists(self::CONFIG_FILE)) {
            self::$whitelist = require_once self::CONFIG_FILE;
        }
    }

    public static function isAllowed(string $ip = null): bool {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? '';
        return in_array($ip, self::$whitelist, true);
    }

    public static function addIP(string $ip): bool {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        if (!in_array($ip, self::$whitelist, true)) {
            self::$whitelist[] = $ip;
            return self::saveWhitelist();
        }
        return true;
    }

    public static function removeIP(string $ip): bool {
        $key = array_search($ip, self::$whitelist, true);
        if ($key !== false) {
            unset(self::$whitelist[$key]);
            return self::saveWhitelist();
        }
        return true;
    }

    private static function saveWhitelist(): bool {
        $content = "<?php\nreturn " . var_export(self::$whitelist, true) . ";\n";
        return file_put_contents(self::CONFIG_FILE, $content) !== false;
    }
}

IPWhitelist::init();
