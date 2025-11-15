<?php
declare(strict_types=1);

/**
 * Tenant Manager - Handles multi-tenant identification and configuration
 */
final class TenantManager
{
    private static array $config = [];
    private static ?string $currentTenant = null;

    public static function init(): void
    {
        self::identifyTenant();
        self::loadConfig();
    }

    private static function identifyTenant(): void
    {
        $header = $_SERVER['HTTP_X_TENANT'] ?? '';
        self::$currentTenant = self::validateTenantId($header) ? $header : 'default';
    }

    public static function validateTenantId(string $id): bool
    {
        return preg_match('/^[a-z0-9\-]{1,32}$/', $id) === 1;
    }

    public static function getCurrentTenantId(): string
    {
        return self::$currentTenant ?? 'default';
    }

    private static function loadConfig(): void
    {
        $configFile = __DIR__ . '/../../../config/tenants/' . self::$currentTenant . '.php';
        self::$config = file_exists($configFile)
            ? require_once $configFile
            : require_once __DIR__ . '/../../../config/tenants/default.php';
    }

    public static function getCurrentTenant(): string
    {
        return self::$currentTenant;
    }

    public static function getConfig(string $key): mixed
    {
        return self::$config[$key] ?? null;
    }
}
