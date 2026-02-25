<?php
declare(strict_types=1);

class JobListing
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];

        if (!empty($filters['status'])) { $where[] = 'j.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category'])) { $where[] = 'j.category = ?'; $params[] = $filters['category']; }
        if (!empty($filters['job_type'])) { $where[] = 'j.job_type = ?'; $params[] = $filters['job_type']; }
        if (!empty($filters['remote_type'])) { $where[] = 'j.remote_type = ?'; $params[] = $filters['remote_type']; }
        if (!empty($filters['experience_level'])) { $where[] = 'j.experience_level = ?'; $params[] = $filters['experience_level']; }
        if (!empty($filters['location'])) { $where[] = 'j.location LIKE ?'; $params[] = '%' . $filters['location'] . '%'; }
        if (!empty($filters['company_name'])) { $where[] = 'j.company_name LIKE ?'; $params[] = '%' . $filters['company_name'] . '%'; }
        if (!empty($filters['salary_min'])) { $where[] = '(j.salary_max >= ? OR j.salary_max IS NULL)'; $params[] = (float)$filters['salary_min']; }
        if (!empty($filters['salary_max'])) { $where[] = '(j.salary_min <= ? OR j.salary_min IS NULL)'; $params[] = (float)$filters['salary_max']; }
        if (!empty($filters['search'])) { $where[] = "MATCH(j.title, j.description, j.requirements, j.company_name, j.location, j.category) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        if (!empty($filters['featured'])) { $where[] = 'j.is_featured = 1'; }

        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_listings j WHERE {$wSql}");
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $orderBy = match ($filters['sort'] ?? '') {
            'newest' => 'j.created_at DESC',
            'salary' => 'j.salary_max DESC',
            'views' => 'j.view_count DESC',
            'expiring' => 'j.expires_at ASC',
            default => 'j.is_featured DESC, j.created_at DESC'
        };

        $stmt = $pdo->prepare("SELECT j.* FROM job_listings j WHERE {$wSql} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) { $r['skills'] = json_decode($r['skills'] ?: '[]', true); }
        return ['listings' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT * FROM job_listings WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['skills'] = json_decode($r['skills'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT * FROM job_listings WHERE slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['skills'] = json_decode($r['skills'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['title'] ?? 'job');
        $skills = ($data['skills'] ?? null) ?: null;
        if (is_string($skills)) { $decoded = json_decode($skills, true); if ($decoded === null && $skills !== 'null') { $skills = json_encode(array_map('trim', explode(',', $skills))); } }
        if (is_array($skills)) { $skills = json_encode($skills); }

        $stmt = $pdo->prepare("INSERT INTO job_listings (title, slug, company_name, company_logo, location, remote_type, job_type, salary_min, salary_max, salary_currency, description, requirements, benefits, category, experience_level, skills, application_url, application_email, is_featured, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'] ?? '',
            $slug,
            ($data['company_name'] ?? null) ?: '',
            ($data['company_logo'] ?? null) ?: null,
            ($data['location'] ?? null) ?: '',
            ($data['remote_type'] ?? null) ?: 'onsite',
            ($data['job_type'] ?? null) ?: 'full-time',
            !empty($data['salary_min']) ? (float)$data['salary_min'] : null,
            !empty($data['salary_max']) ? (float)$data['salary_max'] : null,
            ($data['salary_currency'] ?? null) ?: 'USD',
            ($data['description'] ?? null) ?: '',
            ($data['requirements'] ?? null) ?: '',
            ($data['benefits'] ?? null) ?: '',
            ($data['category'] ?? null) ?: '',
            ($data['experience_level'] ?? null) ?: 'mid',
            $skills,
            ($data['application_url'] ?? null) ?: '',
            ($data['application_email'] ?? null) ?: '',
            (int)($data['is_featured'] ?? 0),
            ($data['status'] ?? null) ?: 'draft',
            !empty($data['expires_at']) ? $data['expires_at'] : null,
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('jobs.listing.created', ['job_id' => $id, 'title' => $data['title'] ?? '']);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['title','company_name','company_logo','location','remote_type','job_type','salary_min','salary_max','salary_currency','description','requirements','benefits','category','experience_level','skills','application_url','application_email','is_featured','status','expires_at'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $val = $data[$f];
                if ($f === 'skills') {
                    if (is_array($val)) { $val = json_encode($val); }
                    elseif (is_string($val)) { $decoded = json_decode($val, true); if ($decoded === null && $val !== 'null' && $val !== '') { $val = json_encode(array_map('trim', explode(',', $val))); } }
                }
                if (in_array($f, ['salary_min', 'salary_max'])) { $val = $val !== '' && $val !== null ? (float)$val : null; }
                if ($f === 'expires_at' && empty($val)) { $val = null; }
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE job_listings SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM job_applications WHERE job_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM job_listings WHERE id = ?")->execute([$id]);
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare("UPDATE job_listings SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='draft') AS draft, SUM(status='expired') AS expired, SUM(is_featured=1) AS featured FROM job_listings")->fetch(\PDO::FETCH_ASSOC);
        $apps = (int)$pdo->query("SELECT COUNT(*) FROM job_applications WHERE status='new'")->fetchColumn();
        $totalApps = (int)$pdo->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();
        $companies = (int)$pdo->query("SELECT COUNT(*) FROM job_companies WHERE status='active'")->fetchColumn();
        return array_merge(array_map('intval', $row), ['new_applications' => $apps, 'total_applications' => $totalApps, 'companies' => $companies]);
    }

    public static function getCategories(): array
    {
        return db()->query("SELECT category, COUNT(*) AS cnt FROM job_listings WHERE status = 'active' AND category != '' GROUP BY category ORDER BY cnt DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getLocations(): array
    {
        return db()->query("SELECT DISTINCT location FROM job_listings WHERE status = 'active' AND location != '' ORDER BY location")->fetchAll(\PDO::FETCH_COLUMN);
    }

    private static function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM job_listings WHERE slug = ?");
            $stmt->execute([$slug]);
            if ((int)$stmt->fetchColumn() === 0) break;
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }
}
