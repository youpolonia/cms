<?php
declare(strict_types=1);

class DirectoryListing
{
    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1']; $params = [];
        if (!empty($filters['status'])) { $where[] = 'l.status = ?'; $params[] = $filters['status']; }
        if (!empty($filters['category_id'])) { $where[] = 'l.category_id = ?'; $params[] = (int)$filters['category_id']; }
        if (!empty($filters['city'])) { $where[] = 'l.city = ?'; $params[] = $filters['city']; }
        if (!empty($filters['search'])) { $where[] = "MATCH(l.title, l.description, l.tags, l.city) AGAINST(? IN BOOLEAN MODE)"; $params[] = $filters['search']; }
        if (!empty($filters['featured'])) { $where[] = 'l.is_featured = 1'; }
        $wSql = implode(' AND ', $where);

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM directory_listings l WHERE {$wSql}");
        $stmt->execute($params); $total = (int)$stmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $orderBy = match($filters['sort'] ?? '') { 'rating' => 'l.avg_rating DESC', 'newest' => 'l.created_at DESC', 'views' => 'l.view_count DESC', default => 'l.is_featured DESC, l.avg_rating DESC' };
        $stmt = $pdo->prepare("SELECT l.*, c.name AS category_name, c.icon AS category_icon FROM directory_listings l LEFT JOIN directory_categories c ON l.category_id = c.id WHERE {$wSql} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}");
        $stmt->execute($params);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($rows as &$r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['hours'] = json_decode($r['hours'] ?: '{}', true); $r['social_links'] = json_decode($r['social_links'] ?: '{}', true); }
        return ['listings' => $rows, 'total' => $total, 'page' => $page, 'pages' => (int)ceil($total / $perPage)];
    }

    public static function get(int $id): ?array
    {
        $stmt = db()->prepare("SELECT l.*, c.name AS category_name FROM directory_listings l LEFT JOIN directory_categories c ON l.category_id = c.id WHERE l.id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['hours'] = json_decode($r['hours'] ?: '{}', true); $r['social_links'] = json_decode($r['social_links'] ?: '{}', true); }
        return $r ?: null;
    }

    public static function getBySlug(string $slug): ?array
    {
        $stmt = db()->prepare("SELECT l.*, c.name AS category_name FROM directory_listings l LEFT JOIN directory_categories c ON l.category_id = c.id WHERE l.slug = ?");
        $stmt->execute([$slug]);
        $r = $stmt->fetch(\PDO::FETCH_ASSOC);
        if ($r) { $r['images'] = json_decode($r['images'] ?: '[]', true); $r['hours'] = json_decode($r['hours'] ?: '{}', true); }
        return $r ?: null;
    }

    public static function create(array $data): int
    {
        $pdo = db();
        $slug = self::generateSlug($data['title'] ?? 'listing');
        foreach (['images','hours','social_links'] as $j) { if (isset($data[$j]) && is_array($data[$j])) $data[$j] = json_encode($data[$j]); }
        $stmt = $pdo->prepare("INSERT INTO directory_listings (title, slug, category_id, description, short_description, owner_email, owner_name, phone, website, address, city, state, zip, country, tags, price_range, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['title'], $slug, !empty($data['category_id']) ? (int)$data['category_id'] : null,
            $data['description'] ?? '', $data['short_description'] ?? '',
            $data['owner_email'] ?? '', $data['owner_name'] ?? '',
            $data['phone'] ?? '', $data['website'] ?? '', $data['address'] ?? '',
            $data['city'] ?? '', $data['state'] ?? '', $data['zip'] ?? '', $data['country'] ?? '',
            $data['tags'] ?? '', $data['price_range'] ?? '', $data['status'] ?? 'pending',
        ]);
        $id = (int)$pdo->lastInsertId();
        if (!empty($data['category_id'])) self::recountCategory((int)$data['category_id']);
        if (function_exists('cms_event')) cms_event('directory.listing.created', ['listing_id' => $id, 'title' => $data['title']]);
        return $id;
    }

    public static function update(int $id, array $data): void
    {
        $pdo = db();
        $allowed = ['title','category_id','description','short_description','owner_email','owner_name','phone','website','address','city','state','zip','country','latitude','longitude','logo','images','hours','social_links','tags','price_range','is_featured','is_verified','status'];
        $fields = []; $params = [];
        foreach ($allowed as $f) {
            if (array_key_exists($f, $data)) {
                $fields[] = "{$f} = ?";
                $val = $data[$f];
                if (in_array($f, ['images','hours','social_links']) && is_array($val)) $val = json_encode($val);
                $params[] = $val;
            }
        }
        if (empty($fields)) return;
        $params[] = $id;
        $pdo->prepare("UPDATE directory_listings SET " . implode(', ', $fields) . " WHERE id = ?")->execute($params);
    }

    public static function delete(int $id): void
    {
        $listing = self::get($id);
        $pdo = db();
        $pdo->prepare("DELETE FROM directory_reviews WHERE listing_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM directory_listings WHERE id = ?")->execute([$id]);
        if ($listing && $listing['category_id']) self::recountCategory((int)$listing['category_id']);
    }

    public static function incrementViews(int $id): void
    {
        db()->prepare("UPDATE directory_listings SET view_count = view_count + 1 WHERE id = ?")->execute([$id]);
    }

    public static function recalcRating(int $id): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM directory_reviews WHERE listing_id = ? AND status = 'approved'");
        $stmt->execute([$id]); $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $pdo->prepare("UPDATE directory_listings SET avg_rating = ?, review_count = ? WHERE id = ?")->execute([round((float)$row['avg_r'], 2), (int)$row['cnt'], $id]);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("SELECT COUNT(*) AS total, SUM(status='active') AS active, SUM(status='pending') AS pending, SUM(is_featured=1) AS featured, SUM(is_claimed=1) AS claimed FROM directory_listings")->fetch(\PDO::FETCH_ASSOC);
        $reviews = (int)$pdo->query("SELECT COUNT(*) FROM directory_reviews WHERE status='pending'")->fetchColumn();
        return array_merge(array_map('intval', $row), ['pending_reviews' => $reviews]);
    }

    private static function recountCategory(int $catId): void
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM directory_listings WHERE category_id = ? AND status = 'active'");
        $stmt->execute([$catId]); $count = (int)$stmt->fetchColumn();
        $pdo->prepare("UPDATE directory_categories SET listing_count = ? WHERE id = ?")->execute([$count, $catId]);
    }

    private static function generateSlug(string $title): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($title)));
        $pdo = db(); $base = $slug; $i = 1;
        while (true) { $stmt = $pdo->prepare("SELECT COUNT(*) FROM directory_listings WHERE slug = ?"); $stmt->execute([$slug]); if ((int)$stmt->fetchColumn() === 0) break; $slug = $base . '-' . (++$i); }
        return $slug;
    }
}
