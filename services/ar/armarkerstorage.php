<?php
declare(strict_types=1);

class ARMarkerStorage {
    private static ?PDO $connection = null;

    public static function initialize(PDO $connection): void {
        self::$connection = $connection;
    }

    public static function store(string $marker, ?DateTime $expiresAt = null): int {
        if (!ARMarker::validate($marker)) {
            throw new InvalidArgumentException('Invalid marker pattern');
        }

        $stmt = self::$connection->prepare(
            "INSERT INTO ar_markers (marker_pattern, expires_at) 
             VALUES (:pattern, :expires)"
        );
        $stmt->execute([
            ':pattern' => $marker,
            ':expires' => $expiresAt?->format('Y-m-d H:i:s')
        ]);

        return (int)self::$connection->lastInsertId();
    }

    public static function getById(int $id): ?array {
        $stmt = self::$connection->prepare(
            "SELECT * FROM ar_markers WHERE id = :id AND is_active = TRUE"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function recordUsage(int $id): void {
        $stmt = self::$connection->prepare(
            "UPDATE ar_markers 
             SET usage_count = usage_count + 1, 
                 last_used_at = CURRENT_TIMESTAMP 
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
    }

    public static function generateAndStore(int $count = 1): array {
        $ids = [];
        for ($i = 0; $i < $count; $i++) {
            $marker = ARMarker::generate(rand(1, PHP_INT_MAX));
            $ids[] = self::store($marker);
        }
        return $ids;
    }

    public static function validateActive(int $id): bool {
        $marker = self::getById($id);
        if (!$marker) {
            return false;
        }

        $now = new DateTime();
        $expiresAt = $marker['expires_at'] 
            ? new DateTime($marker['expires_at']) 
            : null;

        return !$expiresAt || $now < $expiresAt;
    }
}
