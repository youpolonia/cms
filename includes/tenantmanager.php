<?php

class TenantManager {
    private static $currentTenantId = null;
    private static $tenantConfigs = [];

    // Static methods remain unchanged for backward compatibility
    public static function setCurrentTenant(string $tenantId): void {
        self::$currentTenantId = $tenantId;
    }

    public static function getCurrentTenant(): ?string {
        return self::$currentTenantId;
    }

    public static function loadConfig(string $tenantId, array $config): void {
        self::$tenantConfigs[$tenantId] = $config;
    }

    public static function getConfig(string $key, $default = null) {
        if (!self::$currentTenantId || !isset(self::$tenantConfigs[self::$currentTenantId])) {
            return $default;
        }
        return self::$tenantConfigs[self::$currentTenantId][$key] ?? $default;
    }

    public static function isTenantValid(string $tenantId): bool {
        return isset(self::$tenantConfigs[$tenantId]);
    }

    public static function getAllTenants(): array {
        return array_keys(self::$tenantConfigs);
    }

    // New instance methods for dependency injection support
    public function setTenant(string $tenantId): void {
        self::setCurrentTenant($tenantId);
    }

    public function getTenant(): ?string {
        return self::getCurrentTenant();
    }

    public function getTenantConfig(string $key, $default = null) {
        return self::getConfig($key, $default);
    }

    public function validateTenant(string $tenantId): bool {
        return self::isTenantValid($tenantId);
    }

    public function listTenants(): array {
        return self::getAllTenants();
    }
}
