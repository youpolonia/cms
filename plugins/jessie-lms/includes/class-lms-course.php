<?php
declare(strict_types=1);

class LmsCourse
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $where[] = 'category = ?'; $params[] = $filters['category']; }
        if (!empty($filters['search'])) { $where[] = '(title LIKE ? OR description LIKE ?)'; $params[] = '%'.$filters['search'].'%'; $params[] = '%'.$filters['search'].'%'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM lms_courses WHERE {$wSql}");
        $stmt->execute($params); $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("SELECT * FROM lms_courses WHERE {$wSql} ORDER BY featured DESC, created_at DESC LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        return ['courses' => $stmt->fetchAll(\PDO::FETCH_ASSOC), 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM lms_courses WHERE id = ?"); $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM lms_courses WHERE slug = ?"); $stmt->execute([$slug]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['title'] ?? 'course');
        $stmt = $pdo->prepare("INSERT INTO lms_courses (title, slug, description, short_description, instructor_name, instructor_bio, category, difficulty, price, is_free, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'], $slug, $data['description'] ?? '', $data['short_description'] ?? '',
            $data['instructor_name'] ?? '', $data['instructor_bio'] ?? '', $data['category'] ?? '',
            $data['difficulty'] ?? 'all', (float)($data['price'] ?? 0),
            (float)($data['price'] ?? 0) == 0 ? 1 : (int)($data['is_free'] ?? 0),
            $data['status'] ?? 'draft', (int)($data['featured'] ?? 0),
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['title','description','short_description','instructor_name','instructor_bio','thumbnail','category','difficulty','price','is_free','status','featured','duration_hours'];
        $fields = []; $params = [];
        foreach ($allowed as $f) { if (array_key_exists($f, $data)) { $fields[] = "{$f} = ?"; $params[] = $data[$f]; } }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE lms_courses SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM lms_lessons WHERE course_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM lms_courses WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='published') AS published, SUM(status='draft') AS drafts, SUM(enrollment_count) AS enrollments, SUM(completion_count) AS completions FROM lms_courses")->fetch(\PDO::FETCH_ASSOC);
        return array_map(fn($v) => (int)($v ?? 0), $row);
    }

    public static function getCategories(): array
    {
        return db()->query("SELECT DISTINCT category FROM lms_courses WHERE category != '' ORDER BY category")->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function recalcDuration(int $courseId): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COALESCE(SUM(duration_minutes), 0) FROM lms_lessons WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $mins = (int)$stmt->fetchColumn();
        $pdo->prepare("UPDATE lms_courses SET duration_hours = ? WHERE id = ?")->execute([round($mins / 60, 1), $courseId]);
    }

    private static function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $pdo = db(); $base = $slug; $i = 1;
        while (true) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM lms_courses WHERE slug = ?"); $stmt->execute([$slug]); if ((int)$stmt->fetchColumn() === 0) break; $slug = $base . '-' . (++$i); }
        return $slug;
    }
}
