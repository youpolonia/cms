<?php
declare(strict_types=1);

class PortfolioCategory
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        if ($status) {
            $stmt = $pdo->prepare("SELECT * FROM portfolio_categories WHERE status = ? ORDER BY sort_order, name");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query("SELECT * FROM portfolio_categories ORDER BY sort_order, name");
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM portfolio_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM portfolio_categories WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($data['name'] ?? 'category')));
        $slug = trim($slug, '-');
        $base = $slug; $i = 1;
        while (true) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_categories WHERE slug = ?"); $stmt->execute([$slug]); if ((int)$stmt->fetchColumn() === 0) break; $slug = $base . '-' . (++$i); }
        $stmt = $pdo->prepare("INSERT INTO portfolio_categories (name, slug, description, icon, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $slug,
            (($data['description'] ?? null) ?: ''),
            (($data['icon'] ?? null) ?: ''),
            (int)(($data['sort_order'] ?? null) ?: 0),
            (($data['status'] ?? null) ?: 'active'),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name','description','icon','sort_order','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE portfolio_categories SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE portfolio_projects SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM portfolio_categories WHERE id = ?")->execute([$id]);
    }

    public static function getWithCounts(): array
    {
        return db()->query("SELECT c.*, (SELECT COUNT(*) FROM portfolio_projects p WHERE p.category_id = c.id AND p.status = 'published') AS project_count FROM portfolio_categories c ORDER BY c.sort_order, c.name")->fetchAll(\PDO::FETCH_ASSOC);
    }
}
