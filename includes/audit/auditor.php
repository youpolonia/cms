<?php

namespace CMS\Audit;

use CMS\Logging\Logger;

class Auditor
{
    const CREATE = 'create';
    const UPDATE = 'update';
    const DELETE = 'delete';
    const ACCESS = 'access';

    protected static $enabled = true;

    public static function logAction(
        string $action,
        string $entityType,
        string $entityId = null,
        array $changes = [],
        int $userId = null
    ) {
        if (!self::$enabled) {
            return;
        }

        $context = [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'changes' => $changes,
            'user_id' => $userId
        ];

        Logger::info("Audit action: $action", $context, 'audit');
    }

    public static function logDataAccess(
        string $entityType,
        string $entityId = null,
        string $accessType,
        int $userId = null
    ) {
        self::logAction(self::ACCESS, $entityType, $entityId, ['access_type' => $accessType], $userId);
    }

    public static function enable(): void
    {
        self::$enabled = true;
    }

    public static function disable(): void
    {
        self::$enabled = false;
    }

    public static function isEnabled(): bool
    {
        return self::$enabled;
    }
}
