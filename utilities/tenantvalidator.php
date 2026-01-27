<?php
class TenantValidator {
    private static $validTenants = [
        'tenant-a',
        'tenant-b',
        'tenant-c'
    ];

    public static function validate($tenantId) {
        if (empty($tenantId)) {
            return false;
        }
        return in_array($tenantId, self::$validTenants);
    }

    public static function canShare($tenantId, $content) {
        if (!self::validate($tenantId)) {
            return false;
        }
        return isset($content['id']) && !empty($content['id']);
    }
}
