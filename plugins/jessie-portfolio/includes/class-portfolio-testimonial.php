<?php
declare(strict_types=1);

class PortfolioTestimonial
{
    public static function getAll(?string $status = null): array
    {
        $pdo = db();
        if ($status) {
            $stmt = $pdo->prepare("SELECT t.*, p.title AS project_title FROM portfolio_testimonials t LEFT JOIN portfolio_projects p ON t.project_id = p.id WHERE t.status = ? ORDER BY t.created_at DESC");
            $stmt->execute([$status]);
        } else {
            $stmt = $pdo->query("SELECT t.*, p.title AS project_title FROM portfolio_testimonials t LEFT JOIN portfolio_projects p ON t.project_id = p.id ORDER BY t.created_at DESC");
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT t.*, p.title AS project_title FROM portfolio_testimonials t LEFT JOIN portfolio_projects p ON t.project_id = p.id WHERE t.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    public static function getForProject(int $projectId): array
    {
        $stmt = db()->prepare("SELECT * FROM portfolio_testimonials WHERE project_id = ? AND status = 'published' ORDER BY is_featured DESC, created_at DESC");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getFeatured(int $limit = 6): array
    {
        $stmt = db()->prepare("SELECT t.*, p.title AS project_title FROM portfolio_testimonials t LEFT JOIN portfolio_projects p ON t.project_id = p.id WHERE t.status = 'published' AND t.is_featured = 1 ORDER BY t.created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getPending(): array
    {
        return db()->query("SELECT t.*, p.title AS project_title FROM portfolio_testimonials t LEFT JOIN portfolio_projects p ON t.project_id = p.id WHERE t.status = 'pending' ORDER BY t.created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO portfolio_testimonials (project_id, client_name, client_title, client_company, client_photo, content, rating, is_featured, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            (($data['project_id'] ?? null) ?: null),
            $data['client_name'] ?? '',
            (($data['client_title'] ?? null) ?: ''),
            (($data['client_company'] ?? null) ?: ''),
            (($data['client_photo'] ?? null) ?: null),
            (($data['content'] ?? null) ?: ''),
            max(1, min(5, (int)(($data['rating'] ?? null) ?: 5))),
            (int)(($data['is_featured'] ?? null) ?: 0),
            (($data['status'] ?? null) ?: 'pending'),
        ]);
        $id = (int)$pdo->lastInsertId();
        if (function_exists('cms_event')) cms_event('portfolio.testimonial.created', ['testimonial_id' => $id]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $allowed = ['project_id','client_name','client_title','client_company','client_photo','content','rating','is_featured','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $val = $data[$f];
                if ($f === 'project_id') { $val = ($val ?: null); }
                if ($f === 'rating') { $val = max(1, min(5, (int)($val ?: 5))); }
                $fields[] = "{$f} = ?";
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        db()->prepare("UPDATE portfolio_testimonials SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        db()->prepare("DELETE FROM portfolio_testimonials WHERE id = ?")->execute([$id]);
    }

    public static function approve(int $id): void
    {
        db()->prepare("UPDATE portfolio_testimonials SET status = 'published' WHERE id = ?")->execute([$id]);
    }
}
