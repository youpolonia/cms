<?php
/**
 * Jessie CMS — Wishlist
 * Session-based wishlist for products
 */

class ShopWishlist
{
    /**
     * Add a product to the wishlist
     */
    public static function add(string $sessionId, int $productId): bool
    {
        $pdo = db();
        try {
            $stmt = $pdo->prepare(
                "INSERT IGNORE INTO wishlists (session_id, product_id) VALUES (?, ?)"
            );
            return $stmt->execute([$sessionId, $productId]);
        } catch (\Throwable $e) {
            error_log('ShopWishlist::add error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove a product from the wishlist
     */
    public static function remove(string $sessionId, int $productId): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "DELETE FROM wishlists WHERE session_id = ? AND product_id = ?"
        );
        return $stmt->execute([$sessionId, $productId]);
    }

    /**
     * Toggle a product in the wishlist
     */
    public static function toggle(string $sessionId, int $productId): array
    {
        if (self::isInWishlist($sessionId, $productId)) {
            self::remove($sessionId, $productId);
            return ['added' => false];
        }
        self::add($sessionId, $productId);
        return ['added' => true];
    }

    /**
     * Get all wishlisted products with product data
     */
    public static function getAll(string $sessionId, int $page = 1, int $perPage = 12): array
    {
        $pdo = db();

        $countStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM wishlists w
             JOIN products p ON p.id = w.product_id AND p.status = 'active'
             WHERE w.session_id = ?"
        );
        $countStmt->execute([$sessionId]);
        $total = (int)$countStmt->fetchColumn();

        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            "SELECT p.*, c.name as category_name, w.created_at as wishlisted_at
             FROM wishlists w
             JOIN products p ON p.id = w.product_id AND p.status = 'active'
             LEFT JOIN product_categories c ON p.category_id = c.id
             WHERE w.session_id = ?
             ORDER BY w.created_at DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->execute([$sessionId, $perPage, $offset]);
        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return compact('products', 'total', 'page', 'perPage', 'totalPages');
    }

    /**
     * Check if a product is in the wishlist
     */
    public static function isInWishlist(string $sessionId, int $productId): bool
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM wishlists WHERE session_id = ? AND product_id = ?"
        );
        $stmt->execute([$sessionId, $productId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Get wishlist count for a session
     */
    public static function getCount(string $sessionId): int
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT COUNT(*) FROM wishlists WHERE session_id = ?"
        );
        $stmt->execute([$sessionId]);
        return (int)$stmt->fetchColumn();
    }
}
