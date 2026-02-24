<?php
declare(strict_types=1);

class BookingStaff
{
    public static function getAll(string $status = ''): array
    {
        $sql = "SELECT * FROM booking_staff";
        $params = [];
        if ($status) { $sql .= " WHERE status = ?"; $params[] = $status; }
        $sql .= " ORDER BY name ASC";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $staff = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($staff as &$s) {
            $s['services'] = json_decode($s['services'] ?? '[]', true) ?: [];
            $s['schedule'] = json_decode($s['schedule'] ?? '{}', true) ?: [];
        }
        return $staff;
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM booking_staff WHERE id = ?");
        $stmt->execute([$id]);
        $s = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$s) return null;
        $s['services'] = json_decode($s['services'] ?? '[]', true) ?: [];
        $s['schedule'] = json_decode($s['schedule'] ?? '{}', true) ?: [];
        return $s;
    }

    public static function getForService(int $serviceId): array
    {
        $all = self::getAll('active');
        return array_filter($all, fn($s) => in_array($serviceId, $s['services']));
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO booking_staff (name, email, phone, avatar, bio, services, schedule, status) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['name'], $data['email'] ?? '', $data['phone'] ?? '', $data['avatar'] ?? '',
            $data['bio'] ?? '',
            json_encode($data['services'] ?? []),
            json_encode($data['schedule'] ?? []),
            $data['status'] ?? 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        foreach (['name','email','phone','avatar','bio','status'] as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "`{$f}` = ?"; $params[] = $data[$f]; }
        }
        if (isset($data['services'])) { $fields[] = "services = ?"; $params[] = json_encode($data['services']); }
        if (isset($data['schedule'])) { $fields[] = "schedule = ?"; $params[] = json_encode($data['schedule']); }
        if (empty($fields)) return false;
        $params[] = $id;
        return db()->prepare("UPDATE booking_staff SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): bool
    {
        return db()->prepare("DELETE FROM booking_staff WHERE id = ?")->execute([$id]);
    }
}
