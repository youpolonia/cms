<?php
declare(strict_types=1);

/**
 * ShopDigital — Digital product downloads management
 * Handles download tokens, validation, and file delivery
 */
class ShopDigital
{
        /**
     * Ensure the digital_downloads table exists
     */
    public static function ensureTable(): void
    {
        db()->exec("CREATE TABLE IF NOT EXISTS digital_downloads (
            id INT AUTO_INCREMENT PRIMARY KEY,
            token VARCHAR(64) NOT NULL UNIQUE,
            product_id INT NOT NULL,
            order_id INT NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            max_downloads INT DEFAULT 3,
            downloads_count INT DEFAULT 0,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_token (token),
            INDEX idx_order (order_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

/**
     * Create a download token for a digital product purchase
     */
    public static function createDownloadToken(
        int $productId,
        int $orderId,
        string $email,
        int $maxDownloads = 3,
        int $expiresHours = 72
    ): string {
        $pdo = db();
        $token = bin2hex(random_bytes(32)); // 64-char hex token
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiresHours * 3600));

        $stmt = $pdo->prepare(
            "INSERT INTO digital_downloads (token, product_id, order_id, customer_email, max_downloads, expires_at)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$token, $productId, $orderId, $email, $maxDownloads, $expiresAt]);

        return $token;
    }

    /**
     * Process a download request — validate token and return file info or error
     */
    public static function processDownload(string $token): array
    {
        $record = self::getByToken($token);
        if (!$record) {
            return ['ok' => false, 'error' => 'Invalid download token.'];
        }

        // Check expiry
        if (strtotime($record['expires_at']) < time()) {
            return ['ok' => false, 'error' => 'This download link has expired.'];
        }

        // Check download count
        if ((int)$record['downloads_count'] >= (int)$record['max_downloads']) {
            return ['ok' => false, 'error' => 'Maximum number of downloads reached.'];
        }

        // Get product to find file path
        require_once CMS_ROOT . '/core/shop.php';
        $product = \Shop::getProduct((int)$record['product_id']);
        if (!$product || empty($product['digital_file'])) {
            return ['ok' => false, 'error' => 'Digital file not found for this product.'];
        }

        $filePath = $product['digital_file'];
        // If relative path, prepend CMS_ROOT
        if ($filePath[0] !== '/') {
            $filePath = CMS_ROOT . '/' . $filePath;
        }

        if (!file_exists($filePath)) {
            return ['ok' => false, 'error' => 'The digital file is no longer available.'];
        }

        // Increment download count
        $pdo = db();
        $pdo->prepare("UPDATE digital_downloads SET downloads_count = downloads_count + 1 WHERE id = ?")
            ->execute([(int)$record['id']]);

        return [
            'ok' => true,
            'file' => $filePath,
            'filename' => basename($filePath),
        ];
    }

    /**
     * Get all download records for an order
     */
    public static function getOrderDownloads(int $orderId): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT dd.*, p.name as product_name
             FROM digital_downloads dd
             LEFT JOIN products p ON dd.product_id = p.id
             WHERE dd.order_id = ?
             ORDER BY dd.created_at ASC"
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a single download record by token
     */
    public static function getByToken(string $token): ?array
    {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM digital_downloads WHERE token = ?");
        $stmt->execute([$token]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
