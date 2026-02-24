<?php
declare(strict_types=1);

class BookingService
{
    public static function getAll(string $status = ''): array
    {
        $pdo = db();
        $sql = "SELECT * FROM booking_services";
        $params = [];
        if ($status) { $sql .= " WHERE status = ?"; $params[] = $status; }
        $sql .= " ORDER BY sort_order ASC, name ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM booking_services WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM booking_services WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'service');
        $stmt = $pdo->prepare("INSERT INTO booking_services (name, slug, description, duration_minutes, buffer_minutes, price, currency, max_bookings_per_slot, category, image, color, status, sort_order, settings) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['name'], $slug, $data['description'] ?? '', (int)($data['duration_minutes'] ?? 60),
            (int)($data['buffer_minutes'] ?? 15), (float)($data['price'] ?? 0), $data['currency'] ?? 'USD',
            (int)($data['max_bookings_per_slot'] ?? 1), $data['category'] ?? '', $data['image'] ?? '',
            $data['color'] ?? '#6366f1', $data['status'] ?? 'active', (int)($data['sort_order'] ?? 0),
            json_encode($data['settings'] ?? []),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        $allowed = ['name','description','duration_minutes','buffer_minutes','price','currency','max_bookings_per_slot','category','image','color','status','sort_order'];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "`{$f}` = ?";
                $params[] = $data[$f];
            }
        }
        if (isset($data['settings'])) {
            $fields[] = "settings = ?";
            $params[] = is_string($data['settings']) ? $data['settings'] : json_encode($data['settings']);
        }
        if (isset($data['name'])) {
            $fields[] = "slug = ?";
            $params[] = self::generateSlug($data['name'], $id);
        }
        if (empty($fields)) return false;
        $params[] = $id;
        return db()->prepare("UPDATE booking_services SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): bool
    {
        return db()->prepare("DELETE FROM booking_services WHERE id = ?")->execute([$id]);
    }

    public static function count(string $status = 'active'): int
    {
        return (int)db()->prepare("SELECT COUNT(*) FROM booking_services WHERE status = ?")->execute([$status]) ? db()->query("SELECT FOUND_ROWS()")->fetchColumn() : 0;
    }

    private static function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
        $pdo = db();
        $base = $slug;
        $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT id FROM booking_services WHERE slug = ?" . ($excludeId ? " AND id != ?" : ""));
            $params = [$slug];
            if ($excludeId) $params[] = $excludeId;
            $stmt->execute($params);
            if (!$stmt->fetch()) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
