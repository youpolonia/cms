<?php
declare(strict_types=1);

/**
 * Enterprise Scaling - Database Sharding Manager
 * Implements consistent hashing for horizontal partitioning
 */
class ShardingManager {
    private static array $shards = [];
    private static int $virtualNodes = 160;
    private static array $ring = [];

    /**
     * Initialize shard configuration
     */
    public static function init(array $config): void {
        foreach ($config['shards'] as $shard) {
            self::addShard($shard['id'], $shard['range_start'], $shard['range_end']);
        }
        self::buildHashRing();
        self::logEvent("Sharding initialized with " . count(self::$shards) . " shards");
    }

    /**
     * Add a new shard to the configuration
     */
    public static function addShard(string $shardId, int $rangeStart, int $rangeEnd): void {
        self::$shards[$shardId] = [
            'range_start' => $rangeStart,
            'range_end' => $rangeEnd,
            'status' => 'active'
        ];
    }

    /**
     * Get shard ID for a given key
     */
    public static function getShardFor(string $key): string {
        $hash = self::hashKey($key);
        foreach (self::$ring as $node => $shardId) {
            if ($hash <= $node) {
                return $shardId;
            }
        }
        return array_key_first(self::$shards);
    }

    private static function buildHashRing(): void {
        foreach (self::$shards as $shardId => $shard) {
            for ($i = 0; $i < self::$virtualNodes; $i++) {
                $node = self::hashKey("$shardId-$i");
                self::$ring[$node] = $shardId;
            }
        }
        ksort(self::$ring);
    }

    private static function hashKey(string $key): int {
        return crc32($key);
    }

    private static function logEvent(string $message): void {
        file_put_contents(
            __DIR__ . '/../logs/sharding_events.log',
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }
}
