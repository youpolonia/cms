<?php
declare(strict_types=1);

class RealEstateAgent
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        $sql = "SELECT a.*, (SELECT COUNT(*) FROM re_properties WHERE agent_id = a.id AND status = 'active') AS property_count FROM re_agents a";
        if ($status) { $sql .= " WHERE a.status = ?"; }
        $sql .= " ORDER BY a.name";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($status ? [$status] : []);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT a.*, (SELECT COUNT(*) FROM re_properties WHERE agent_id = a.id AND status = 'active') AS property_count FROM re_agents a WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT a.*, (SELECT COUNT(*) FROM re_properties WHERE agent_id = a.id AND status = 'active') AS property_count FROM re_agents a WHERE a.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::makeSlug($data['name'] ?? 'agent');
        $stmt = $pdo->prepare("INSERT INTO re_agents (name, slug, email, phone, photo, bio, license_number, specialties, status) VALUES (?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $data['name'],
            $slug,
            ($data['email'] ?? null) ?: null,
            ($data['phone'] ?? null) ?: null,
            ($data['photo'] ?? null) ?: null,
            ($data['bio'] ?? null) ?: null,
            ($data['license_number'] ?? null) ?: null,
            ($data['specialties'] ?? null) ?: null,
            $data['status'] ?? 'active',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('realestate.agent.created', ['id' => $id, 'name' => $data['name']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['name','email','phone','photo','bio','license_number','specialties','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE re_agents SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("UPDATE re_properties SET agent_id = NULL WHERE agent_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM re_agents WHERE id = ?")->execute([$id]);
    }

    private static function makeSlug(string $text): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($text)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM re_agents WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
