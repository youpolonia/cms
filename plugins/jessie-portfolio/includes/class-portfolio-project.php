<?php
declare(strict_types=1);

class PortfolioProject
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 12): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'p.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category_id'])) { $where[] = 'p.category_id = ?'; $params[] = (int)$filters['category_id']; }
        if (!empty($filters['search'])) { $where[] = "MATCH(p.title, p.description, p.short_description, p.client_name) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        if (!empty($filters['featured'])) { $where[] = 'p.is_featured = 1'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_projects p WHERE {$wSql}");
        $stmt->execute($params); $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $orderBy = match ($filters['sort'] ?? '') {
            'newest' => 'p.created_at DESC',
            'views' => 'p.view_count DESC',
            'title' => 'p.title ASC',
            'date' => 'p.completion_date DESC',
            default => 'p.sort_order ASC, p.is_featured DESC, p.created_at DESC'
        };
        $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug, c.icon AS category_icon FROM portfolio_projects p LEFT JOIN portfolio_categories c ON p.category_id = c.id WHERE {$wSql} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['images'] = json_decode($r['images'] ?: '[]', true);
            $r['technologies'] = json_decode($r['technologies'] ?: '[]', true);
        }
        return ['projects' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM portfolio_projects p LEFT JOIN portfolio_categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['technologies'] = json_decode($r['technologies'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM portfolio_projects p LEFT JOIN portfolio_categories c ON p.category_id = c.id WHERE p.slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['technologies'] = json_decode($r['technologies'] ?: '[]', true); }
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['title'] ?? 'project');
        $technologies = ($data['technologies'] ?? null) ?: null;
        if (is_string($technologies)) {
            $technologies = json_encode(array_map('trim', explode(',', $technologies)));
        } elseif (is_array($technologies)) {
            $technologies = json_encode($technologies);
        }
        $images = ($data['images'] ?? null) ?: null;
        if (is_string($images) && !str_starts_with($images, '[')) {
            $images = json_encode(array_map('trim', explode(',', $images)));
        } elseif (is_array($images)) {
            $images = json_encode($images);
        }
        $stmt = $pdo->prepare("INSERT INTO portfolio_projects (title, slug, category_id, client_name, description, short_description, cover_image, images, technologies, project_url, completion_date, is_featured, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'],
            $slug,
            (($data['category_id'] ?? null) ?: null),
            (($data['client_name'] ?? null) ?: ''),
            (($data['description'] ?? null) ?: ''),
            (($data['short_description'] ?? null) ?: ''),
            (($data['cover_image'] ?? null) ?: null),
            $images,
            $technologies,
            (($data['project_url'] ?? null) ?: ''),
            (($data['completion_date'] ?? null) ?: null),
            (int)(($data['is_featured'] ?? null) ?: 0),
            (int)(($data['sort_order'] ?? null) ?: 0),
            (($data['status'] ?? null) ?: 'draft'),
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('portfolio.project.created', ['project_id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['title','category_id','client_name','description','short_description','cover_image','images','technologies','project_url','completion_date','is_featured','sort_order','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $val = $data[$f];
                if ($f === 'technologies') {
                    if (is_string($val) && $val !== '') {
                        $val = json_encode(array_map('trim', explode(',', $val)));
                    } elseif (is_array($val)) {
                        $val = json_encode($val);
                    } else {
                        $val = null;
                    }
                }
                if ($f === 'images') {
                    if (is_string($val) && $val !== '' && !str_starts_with($val, '[')) {
                        $val = json_encode(array_map('trim', explode(',', $val)));
                    } elseif (is_array($val)) {
                        $val = json_encode($val);
                    } elseif ($val === '') {
                        $val = null;
                    }
                }
                if ($f === 'category_id') { $val = ($val ?: null); }
                if ($f === 'completion_date') { $val = ($val ?: null); }
                $fields[] = "{$f} = ?";
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE portfolio_projects SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
        if (function_exists('cms_event')) cms_event('portfolio.project.updated', ['project_id' => $id]);
    }

    public static function delete(int $id): void
    {
        $pdo = db();
        $pdo->prepare("DELETE FROM portfolio_testimonials WHERE project_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM portfolio_projects WHERE id = ?")->execute([$id]);
        if (function_exists('cms_event')) cms_event('portfolio.project.deleted', ['project_id' => $id]);
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare("UPDATE portfolio_projects SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='published') AS published, SUM(status='draft') AS draft, SUM(is_featured=1) AS featured, SUM(view_count) AS total_views FROM portfolio_projects")->fetch(\PDO::FETCH_ASSOC);
        $cats = (int)$pdo->query("SELECT COUNT(*) FROM portfolio_categories WHERE status='active'")->fetchColumn();
        $testimonials = (int)$pdo->query("SELECT COUNT(*) FROM portfolio_testimonials WHERE status='pending'")->fetchColumn();
        $totalTestimonials = (int)$pdo->query("SELECT COUNT(*) FROM portfolio_testimonials")->fetchColumn();
        return array_merge(array_map('intval', $row), ['categories' => $cats, 'pending_testimonials' => $testimonials, 'total_testimonials' => $totalTestimonials]);
    }

    public static function getRelated(int $projectId, int $categoryId, int $limit = 3): array
    {
        $stmt = db()->prepare("SELECT p.*, c.name AS category_name FROM portfolio_projects p LEFT JOIN portfolio_categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? AND p.status = 'published' ORDER BY p.is_featured DESC, p.sort_order ASC LIMIT ?");
        $stmt->execute([$categoryId, $projectId, $limit]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['images'] = json_decode($r['images'] ?: '[]', true);
            $r['technologies'] = json_decode($r['technologies'] ?: '[]', true);
        }
        return $rows;
    }

    private static function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $slug = trim($slug, '-');
        $pdo = db(); $base = $slug; $i = 1;
        while (true) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM portfolio_projects WHERE slug = ?"); $stmt->execute([$slug]); if ((int)$stmt->fetchColumn() === 0) break; $slug = $base . '-' . (++$i); }
        return $slug;
    }
}
