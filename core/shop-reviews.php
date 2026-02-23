<?php
declare(strict_types=1);

/**
 * ShopReviews — Product Review & Rating System
 * Pure PHP, no frameworks
 */
class ShopReviews
{
    /**
     * Submit a new review (status = pending)
     */
    public static function submit(array $data): int
    {
        $pdo = db();

        $productId     = (int)($data['product_id'] ?? 0);
        $customerName  = trim($data['customer_name'] ?? '');
        $customerEmail = trim($data['customer_email'] ?? '');
        $rating        = (int)($data['rating'] ?? 0);
        $title         = trim($data['title'] ?? '');
        $reviewText    = trim($data['review_text'] ?? '');

        if ($productId < 1 || $rating < 1 || $rating > 5 || $customerName === '' || $customerEmail === '') {
            return 0;
        }

        // Check for verified purchase
        $isVerified = 0;
        try {
            $stmt = $pdo->prepare("SELECT id, items FROM orders WHERE customer_email = ? AND status NOT IN ('cancelled') LIMIT 50");
            $stmt->execute([$customerEmail]);
            while ($order = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $items = json_decode($order['items'] ?? '[]', true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        if ((int)($item['product_id'] ?? $item['id'] ?? 0) === $productId) {
                            $isVerified = 1;
                            break 2;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // orders table might not have customer_email — skip
        }

        $ins = $pdo->prepare("
            INSERT INTO product_reviews (product_id, customer_name, customer_email, rating, title, review_text, status, is_verified_purchase, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
        ");
        $ins->execute([$productId, $customerName, $customerEmail, $rating, $title, $reviewText, $isVerified]);
        $id = (int)$pdo->lastInsertId();

        if ($id > 0 && function_exists('cms_event')) {
            cms_event('shop.review.submitted', [
                'id' => $id,
                'product_id' => $productId,
                'customer_name' => $customerName,
                'rating' => $rating,
            ]);
        }

        return $id;
    }

    public static function approve(int $id): bool
    {
        $stmt = db()->prepare("UPDATE product_reviews SET status = 'approved' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function reject(int $id): bool
    {
        $stmt = db()->prepare("UPDATE product_reviews SET status = 'rejected' WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function delete(int $id): bool
    {
        $stmt = db()->prepare("DELETE FROM product_reviews WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function reply(int $id, string $text): bool
    {
        $stmt = db()->prepare("UPDATE product_reviews SET admin_reply = ? WHERE id = ?");
        return $stmt->execute([trim($text), $id]);
    }

    public static function markHelpful(int $id): bool
    {
        $stmt = db()->prepare("UPDATE product_reviews SET helpful_count = helpful_count + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function getForProduct(int $productId, int $page = 1, int $perPage = 10): array
    {
        $pdo = db();
        $offset = ($page - 1) * $perPage;

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews WHERE product_id = ? AND status = 'approved'");
        $countStmt->execute([$productId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $pdo->prepare("
            SELECT * FROM product_reviews
            WHERE product_id = ? AND status = 'approved'
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$productId, $perPage, $offset]);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'reviews' => $reviews,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / max($perPage, 1)),
        ];
    }

    public static function getProductRating(int $productId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare("
            SELECT rating, COUNT(*) as cnt
            FROM product_reviews
            WHERE product_id = ? AND status = 'approved'
            GROUP BY rating
        ");
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $total = 0;
        $sum = 0;
        foreach ($rows as $row) {
            $r = (int)$row['rating'];
            $c = (int)$row['cnt'];
            $distribution[$r] = $c;
            $total += $c;
            $sum += $r * $c;
        }

        return [
            'average' => $total > 0 ? round($sum / $total, 1) : 0.0,
            'count' => $total,
            'distribution' => $distribution,
        ];
    }

    public static function getAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $pdo = db();
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'r.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['product_id'])) {
            $where[] = 'r.product_id = ?';
            $params[] = (int)$filters['product_id'];
        }
        if (!empty($filters['rating'])) {
            $where[] = 'r.rating = ?';
            $params[] = (int)$filters['rating'];
        }

        $whereStr = implode(' AND ', $where);

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM product_reviews r WHERE {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $dataParams = array_merge($params, [$perPage, $offset]);
        $stmt = $pdo->prepare("
            SELECT r.*, p.name AS product_name, p.slug AS product_slug
            FROM product_reviews r
            LEFT JOIN products p ON p.id = r.product_id
            WHERE {$whereStr}
            ORDER BY r.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($dataParams);
        $reviews = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return [
            'reviews' => $reviews,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => (int)ceil($total / max($perPage, 1)),
        ];
    }

    public static function getRecent(int $limit = 5): array
    {
        $stmt = db()->prepare("
            SELECT r.*, p.name AS product_name, p.slug AS product_slug
            FROM product_reviews r
            LEFT JOIN products p ON p.id = r.product_id
            ORDER BY r.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function getStats(): array
    {
        $pdo = db();
        $row = $pdo->query("
            SELECT
                COUNT(*) AS total,
                SUM(status = 'pending') AS pending,
                SUM(status = 'approved') AS approved,
                SUM(status = 'rejected') AS rejected,
                ROUND(AVG(CASE WHEN status = 'approved' THEN rating END), 1) AS avg_rating
            FROM product_reviews
        ")->fetch(\PDO::FETCH_ASSOC);

        return [
            'total' => (int)($row['total'] ?? 0),
            'pending' => (int)($row['pending'] ?? 0),
            'approved' => (int)($row['approved'] ?? 0),
            'rejected' => (int)($row['rejected'] ?? 0),
            'avg_rating' => (float)($row['avg_rating'] ?? 0),
        ];
    }
}
