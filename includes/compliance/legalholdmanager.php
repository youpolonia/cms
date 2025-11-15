<?php
declare(strict_types=1);

/**
 * Compliance - Legal Hold Manager
 * Handles data preservation for legal/compliance purposes
 */
class LegalHoldManager {
    private static array $activeHolds = [];
    private static string $logFile = __DIR__ . '/../../logs/legal_holds.log';

    /**
     * Place a legal hold on specific data
     */
    public static function placeHold(
        string $holdId,
        string $description,
        array $dataTypes,
        string $reason,
        string $requestedBy
    ): bool {
        if (isset(self::$activeHolds[$holdId])) {
            return false;
        }

        self::$activeHolds[$holdId] = [
            'description' => $description,
            'data_types' => $dataTypes,
            'created_at' => time(),
            'requested_by' => $requestedBy,
            'reason' => $reason
        ];

        self::logEvent("Legal hold placed: $holdId");
        RetentionPolicyManager::setLegalHold($holdId, true);
        
        return true;
    }

    /**
     * Release a legal hold
     */
    public static function releaseHold(string $holdId, string $releasedBy): bool {
        if (!isset(self::$activeHolds[$holdId])) {
            return false;
        }

        self::$activeHolds[$holdId]['released_at'] = time();
        self::$activeHolds[$holdId]['released_by'] = $releasedBy;
        
        RetentionPolicyManager::setLegalHold($holdId, false);
        self::logEvent("Legal hold released: $holdId");

        return true;
    }

    /**
     * Check if a data type is under legal hold
     */
    public static function isUnderHold(string $dataType): bool {
        foreach (self::$activeHolds as $hold) {
            if (in_array($dataType, $hold['data_types']) && !isset($hold['released_at'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get all active legal holds
     */
    public static function getActiveHolds(): array {
        return array_filter(self::$activeHolds, function($hold) {
            return !isset($hold['released_at']);
        });
    }

    private static function logEvent(string $message): void {
        file_put_contents(
            self::$logFile,
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }

    // BREAKPOINT: Continue with data preservation methods
}
