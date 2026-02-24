<?php
declare(strict_types=1);

class MembershipPlan
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        $sql = "SELECT * FROM membership_plans";
        if ($status) { $sql .= " WHERE status = ?"; $stmt = $pdo->prepare($sql); $stmt->execute([$status]); }
        else { $stmt = $pdo->query($sql . " ORDER BY sort_order, price"); }
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) { $r['features'] = json_decode($r['features'] ?: '[]', true); $r['content_access'] = json_decode($r['content_access'] ?: '[]', true); }
        return $rows;
    }

    public static function get(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['features'] = json_decode($r['features'] ?: '[]', true); $r['content_access'] = json_decode($r['content_access'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'plan');
        $features = is_array($data['features'] ?? null) ? json_encode($data['features']) : ($data['features'] ?? '[]');
        $stmt = $pdo->prepare("INSERT INTO membership_plans (name, slug, description, price, billing_period, trial_days, features, color, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'], $slug, $data['description'] ?? '', (float)($data['price'] ?? 0),
            $data['billing_period'] ?? 'monthly', (int)($data['trial_days'] ?? 0),
            $features, $data['color'] ?? '#6366f1', (int)($data['sort_order'] ?? 0), $data['status'] ?? 'active'
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['name','description','price','billing_period','trial_days','features','color','sort_order','status','content_access'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $val = $data[$f];
                if (in_array($f, ['features','content_access']) && is_array($val)) $val = json_encode($val);
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE membership_plans SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM membership_plans WHERE id = ?")->execute([$id]);
    }

    public static function getPublicPlans(): array
    {
        return self::getAll('active');
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $pdo = db(); $base = $slug; $i = 1;
        while (true) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM membership_plans WHERE slug = ?"); $stmt->execute([$slug]); if ((int)$stmt->fetchColumn() === 0) break; $slug = $base . '-' . (++$i); }
        return $slug;
    }
}
