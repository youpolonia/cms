<?php
declare(strict_types=1);

class AffiliateProgram
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'p.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['search'])) { $where[] = '(p.name LIKE ? OR p.description LIKE ?)'; $params[] = '%' . $filters['search'] . '%'; $params[] = '%' . $filters['search'] . '%'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliate_programs p WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT p.*, (SELECT COUNT(*) FROM affiliates a WHERE a.program_id = p.id) AS affiliate_count FROM affiliate_programs p WHERE {$wSql} ORDER BY p.created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return ['programs' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM affiliate_programs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM affiliate_programs WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getActive(): array
    {
        return db()->query("SELECT * FROM affiliate_programs WHERE status = 'active' ORDER BY name ASC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'program');
        $stmt = $pdo->prepare("INSERT INTO affiliate_programs (name, slug, description, commission_type, commission_value, cookie_days, min_payout, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $slug,
            ($data['description'] ?? null) ?: null,
            ($data['commission_type'] ?? null) ?: 'percentage',
            (float)($data['commission_value'] ?? 0),
            (int)($data['cookie_days'] ?? 30),
            (float)($data['min_payout'] ?? 50),
            ($data['status'] ?? null) ?: 'active',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('affiliate.program.created', ['program_id' => $id, 'name' => $data['name']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name','description','commission_type','commission_value','cookie_days','min_payout','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE affiliate_programs SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM affiliate_conversions WHERE program_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM affiliates WHERE program_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM affiliate_programs WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $programs = (int)$pdo->query("SELECT COUNT(*) FROM affiliate_programs WHERE status='active'")->fetchColumn();
        $affiliates = (int)$pdo->query("SELECT COUNT(*) FROM affiliates")->fetchColumn();
        $activeAffiliates = (int)$pdo->query("SELECT COUNT(*) FROM affiliates WHERE status='active'")->fetchColumn();
        $pendingAffiliates = (int)$pdo->query("SELECT COUNT(*) FROM affiliates WHERE status='pending'")->fetchColumn();
        $totalClicks = (int)$pdo->query("SELECT COALESCE(SUM(total_clicks),0) FROM affiliates")->fetchColumn();
        $totalConversions = (int)$pdo->query("SELECT COALESCE(SUM(total_conversions),0) FROM affiliates")->fetchColumn();
        $totalEarnings = (float)$pdo->query("SELECT COALESCE(SUM(total_earnings),0) FROM affiliates")->fetchColumn();
        $pendingPayouts = (float)$pdo->query("SELECT COALESCE(SUM(pending_payout),0) FROM affiliates")->fetchColumn();
        $pendingConversions = (int)$pdo->query("SELECT COUNT(*) FROM affiliate_conversions WHERE status='pending'")->fetchColumn();
        $totalPaid = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM affiliate_payouts WHERE status='completed'")->fetchColumn();

        return [
            'programs' => $programs,
            'affiliates' => $affiliates,
            'active_affiliates' => $activeAffiliates,
            'pending_affiliates' => $pendingAffiliates,
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'total_earnings' => $totalEarnings,
            'pending_payouts' => $pendingPayouts,
            'pending_conversions' => $pendingConversions,
            'total_paid' => $totalPaid,
        ];
    }

    private static function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM affiliate_programs WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
