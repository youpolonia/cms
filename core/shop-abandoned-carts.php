<?php
declare(strict_types=1);

/**
 * Abandoned Cart Recovery for Jessie AI CMS
 * Tracks abandoned carts and sends reminder emails
 */

require_once CMS_ROOT . '/core/shop.php';

class AbandonedCarts
{
    /**
     * Ensure the abandoned_carts table exists
     */
    public static function ensureTable(): void
    {
        db()->exec("CREATE TABLE IF NOT EXISTS abandoned_carts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(128) NOT NULL,
            customer_email VARCHAR(255) DEFAULT NULL,
            items JSON NOT NULL,
            subtotal DECIMAL(10,2) DEFAULT 0,
            reminder_sent_at DATETIME DEFAULT NULL,
            reminder_count INT DEFAULT 0,
            recovered TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_session (session_id),
            INDEX idx_email (customer_email),
            INDEX idx_reminder (reminder_sent_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /**
     * Upsert cart snapshot
     */
    public static function saveCart(string $sessionId, array $items, float $subtotal, ?string $email = null): void
    {
        if (empty($items)) {
            return;
        }

        $pdo = db();
        $itemsJson = json_encode($items);

        $stmt = $pdo->prepare("SELECT id FROM abandoned_carts WHERE session_id = ? AND recovered = 0 ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$sessionId]);
        $existing = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($existing) {
            $sql = "UPDATE abandoned_carts SET items = ?, subtotal = ?, updated_at = NOW()";
            $params = [$itemsJson, $subtotal];
            if ($email !== null && $email !== '') {
                $sql .= ", customer_email = ?";
                $params[] = $email;
            }
            $sql .= " WHERE id = ?";
            $params[] = (int)$existing['id'];
            $pdo->prepare($sql)->execute($params);
        } else {
            $pdo->prepare(
                "INSERT INTO abandoned_carts (session_id, customer_email, items, subtotal) VALUES (?, ?, ?, ?)"
            )->execute([$sessionId, $email, $itemsJson, $subtotal]);
        }
    }

    /**
     * Mark cart as recovered when order is placed
     */
    public static function markRecovered(string $sessionId): void
    {
        db()->prepare(
            "UPDATE abandoned_carts SET recovered = 1, updated_at = NOW() WHERE session_id = ? AND recovered = 0"
        )->execute([$sessionId]);
    }

    /**
     * Get abandoned carts eligible for reminders
     */
    public static function getAbandoned(int $hoursOld = 1, int $limit = 50): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT * FROM abandoned_carts 
             WHERE customer_email IS NOT NULL 
               AND customer_email != ''
               AND recovered = 0 
               AND reminder_count < 3
               AND updated_at < DATE_SUB(NOW(), INTERVAL ? HOUR)
             ORDER BY updated_at ASC
             LIMIT ?"
        );
        $stmt->execute([$hoursOld, $limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Send reminder emails for abandoned carts
     */
    public static function sendReminders(): array
    {
        require_once CMS_ROOT . '/core/shop-emails.php';

        $carts = self::getAbandoned(1, 50);
        $sent = 0;
        $failed = 0;
        $pdo = db();

        foreach ($carts as $cart) {
            $items = json_decode($cart['items'], true);
            if (!is_array($items) || empty($items)) {
                continue;
            }

            $emailItems = [];
            foreach ($items as $cartKey => $qty) {
                $parts = explode(':', (string)$cartKey);
                $productId = (int)$parts[0];
                $product = \Shop::getProduct($productId);
                if ($product) {
                    $emailItems[] = [
                        'name' => $product['name'],
                        'price' => \Shop::getEffectivePrice($product),
                        'quantity' => (int)$qty,
                        'image' => $product['image'] ?? null,
                    ];
                }
            }

            if (empty($emailItems)) {
                continue;
            }

            $customerName = explode('@', $cart['customer_email'])[0];

            $ok = \ShopEmails::sendAbandonedCartEmail(
                $cart['customer_email'],
                $customerName,
                $emailItems
            );

            if ($ok) {
                $pdo->prepare(
                    "UPDATE abandoned_carts SET reminder_count = reminder_count + 1, reminder_sent_at = NOW(), updated_at = NOW() WHERE id = ?"
                )->execute([(int)$cart['id']]);
                $sent++;
            } else {
                $failed++;
            }
        }

        return [
            'sent' => $sent,
            'failed' => $failed,
            'total_processed' => count($carts),
        ];
    }

    /**
     * Get statistics
     */
    public static function getStats(): array
    {
        $pdo = db();

        $totalAbandoned = (int)$pdo->query("SELECT COUNT(*) FROM abandoned_carts")->fetchColumn();
        $recovered = (int)$pdo->query("SELECT COUNT(*) FROM abandoned_carts WHERE recovered = 1")->fetchColumn();
        $pending = (int)$pdo->query(
            "SELECT COUNT(*) FROM abandoned_carts WHERE recovered = 0 AND customer_email IS NOT NULL AND customer_email != ''"
        )->fetchColumn();
        $rate = $totalAbandoned > 0 ? round(($recovered / $totalAbandoned) * 100, 1) : 0;

        return [
            'total_abandoned' => $totalAbandoned,
            'recovered' => $recovered,
            'pending' => $pending,
            'recovery_rate' => $rate,
        ];
    }

    /**
     * Get all carts for admin listing
     */
    public static function getAll(int $page = 1, int $perPage = 20): array
    {
        $pdo = db();

        $total = (int)$pdo->query("SELECT COUNT(*) FROM abandoned_carts")->fetchColumn();
        $totalPages = max(1, (int)ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));
        $offset = ($page - 1) * $perPage;

        $stmt = $pdo->prepare(
            "SELECT * FROM abandoned_carts ORDER BY created_at DESC LIMIT ? OFFSET ?"
        );
        $stmt->execute([$perPage, $offset]);
        $carts = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return compact('carts', 'total', 'page', 'perPage', 'totalPages');
    }
}

// Ensure table on load
AbandonedCarts::ensureTable();
