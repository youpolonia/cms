<?php
declare(strict_types=1);

class JobCompany
{
    public static function getAll(string $status = ''): array
    {
        $pdo = db();
        if ($status) {
            $stmt = $pdo->prepare("SELECT c.*, (SELECT COUNT(*) FROM job_listings j WHERE j.company_name = c.name AND j.status = 'active') AS job_count FROM job_companies c WHERE c.status = ? ORDER BY c.name ASC");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM job_listings j WHERE j.company_name = c.name AND j.status = 'active') AS job_count FROM job_companies c ORDER BY c.name ASC");
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM job_companies WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM job_companies WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['name'] ?? 'company');
        $stmt = $pdo->prepare("INSERT INTO job_companies (name, slug, logo, description, website, industry, size, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'] ?? '',
            $slug,
            ($data['logo'] ?? null) ?: null,
            ($data['description'] ?? null) ?: '',
            ($data['website'] ?? null) ?: '',
            ($data['industry'] ?? null) ?: '',
            ($data['size'] ?? null) ?: '',
            ($data['location'] ?? null) ?: '',
            ($data['status'] ?? null) ?: 'active',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['name', 'logo', 'description', 'website', 'industry', 'size', 'location', 'status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $params[] = $data[$f];
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE job_companies SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM job_companies WHERE id = ?")->execute([$id]);
    }

    private static function generateSlug(string $name): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($name)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_companies WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
