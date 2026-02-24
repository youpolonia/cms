<?php
declare(strict_types=1);

class NewsletterList
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        $sql = "SELECT * FROM newsletter_lists";
        if ($status) { $sql .= " WHERE status = ?"; $stmt = $pdo->prepare($sql); $stmt->execute([$status]); }
        else { $stmt = $pdo->query($sql . " ORDER BY created_at DESC"); }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM newsletter_lists WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'list');
        $stmt = $pdo->prepare("INSERT INTO newsletter_lists (name, slug, description, color, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$data['name'], $slug, $data['description'] ?? '', $data['color'] ?? '#6366f1', $data['status'] ?? 'active']);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $fields = []; $params = [];
        foreach (['name', 'description', 'color', 'status'] as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE newsletter_lists SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM newsletter_lists WHERE id = ?")->execute([$id]);
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $slug = trim($slug, '-');
        $pdo = db();
        $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM newsletter_lists WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
