<?php
declare(strict_types=1);

class ContentVersionManager {
    private static array $versionStrategies = [
        'timestamp' => 'resolveByTimestamp',
        'manual' => 'resolveManually',
        'branch' => 'createVersionBranch'
    ];

    public static function createVersionHash(string $content): string {
        return hash('sha256', $content);
    }

    public static function resolveConflict(
        string $strategy, 
        array $versionA, 
        array $versionB
    ): array {
        if (!isset(self::$versionStrategies[$strategy])) {
            throw new InvalidArgumentException("Invalid version strategy");
        }
        
        return call_user_func(
            [self::class, self::$versionStrategies[$strategy]],
            $versionA,
            $versionB
        );
    }

    private static function resolveByTimestamp(array $a, array $b): array {
        return ($a['timestamp'] > $b['timestamp']) ? $a : $b;
    }
}
