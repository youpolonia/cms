<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/config.php';

final class ConfigManager
{
    private const SETTINGS_PATH = '/config/settings.json';

    private static function path(): string
    {
        return dirname(__DIR__, 2) . self::SETTINGS_PATH;
    }

    public static function load(): array
    {
        $file = self::path();
        if (!is_file($file)) {
            return [];
        }
        $json = @file_get_contents($file);
        if ($json === false) {
            return [];
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : [];
    }

    public static function get(string $key, $default = null)
    {
        $data = self::load();
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    public static function save(array $data): bool
    {
        $file = self::path();
        $dir = dirname($file);
        if (!is_dir($dir)) {
            return false;
        }
        $tmp = $file . '.tmp';
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            return false;
        }
        if (@file_put_contents($tmp, $json, LOCK_EX) === false) {
            return false;
        }
        @chmod($tmp, 0640);
        return @rename($tmp, $file);
    }

    public static function set(string $key, $value): bool
    {
        $data = self::load();
        $data[$key] = $value;
        return self::save($data);
    }
}
