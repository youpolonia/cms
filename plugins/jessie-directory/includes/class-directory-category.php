<?php
declare(strict_types=1);

class DirectoryCategory
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        $sql = "SELECT * FROM directory_categories";
        if ($status) { $sql .= " WHERE status = ?"; $stmt = $pdo->prepare($sql); $stmt->execute([$status]); }
        else { $stmt = $pdo->query($sql . " ORDER BY sort_order, name"); }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM directory_categories WHERE id = ?"); $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($data['name'] ?? 'category')));
        $stmt = $pdo->prepare("INSERT INTO directory_categories (name, slug, parent_id, description, icon, color, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $slug, ($data['parent_id'] ?? null) ?: null, $data['description'] ?? '', $data['icon'] ?? '', $data['color'] ?? '#6366f1', (int)($data['sort_order'] ?? 0), $data['status'] ?? 'active']);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name','description','icon','color','sort_order','status','parent_id'];
        $fields = []; $params = [];
        foreach ($allowed as $f) { if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; } }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE directory_categories SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM directory_categories WHERE id = ?")->execute([$id]);
    }

    public static function getTree(): array
    {
        $all = self::getAll('active');
        $tree = []; $map = [];
        foreach ($all as $c) $map[$c['id']] = $c + ['children' => []];
        foreach ($map as &$c) {
            if ($c['parent_id'] && isset($map[$c['parent_id']])) $map[$c['parent_id']]['children'][] = &$c;
            else $tree[] = &$c;
        }
        return $tree;
    }
}
