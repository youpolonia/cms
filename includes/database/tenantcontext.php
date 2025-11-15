<?php

namespace Includes\Database;

use InvalidArgumentException;

class TenantContext
{
    protected static ?int $currentTenantId = null;
    protected static ?array $currentTenantConfig = null;
    protected static array $tenantConnections = [];

    public static function setCurrentTenantId(int $tenantId): void
    {
        if (!self::isValidTenantId($tenantId)) {
            throw new InvalidArgumentException("Invalid tenant ID: $tenantId");
        }

        self::$currentTenantId = $tenantId;
        self::$currentTenantConfig = self::loadTenantConfig($tenantId);
    }

    public static function getCurrentTenantId(): ?int
    {
        return self::$currentTenantId;
    }

    public static function getCurrentTenantConfig(): ?array
    {
        return self::$currentTenantConfig;
    }

    public static function getConnectionForTenant(?int $tenantId = null): ?string
    {
        $tenantId = $tenantId ?? self::$currentTenantId;
        return self::$tenantConnections[$tenantId] ?? null;
    }

    public static function clear(): void
    {
        self::$currentTenantId = null;
        self::$currentTenantConfig = null;
    }

    protected static function isValidTenantId(int $tenantId): bool
    {
        // Check if tenant exists in database or config
        $tenants = require_once __DIR__ . '/../../config/tenants.php';
        return isset($tenants[$tenantId]);
    }

    protected static function loadTenantConfig(int $tenantId): array
    {
        $tenants = require_once __DIR__ . '/../../config/tenants.php';
        return $tenants[$tenantId] ?? [];
    }
}
