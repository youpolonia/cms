<?php
/**
 * Jessie CMS — Shop ↔ CRM Integration
 * Syncs orders to CRM contacts, tracks CLV, auto-tags VIPs
 */

class ShopCRM
{
    /**
     * Sync an order to CRM — create/update contact, add activity, calculate CLV
     */
    public static function syncOrderToContact(int $orderId): void
    {
        try {
            $pdo = db();
            $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$order || empty($order['customer_email'])) {
                return;
            }

            require_once CMS_ROOT . '/core/crm_manager.php';

            $email = trim($order['customer_email']);
            $name = trim($order['customer_name'] ?? '');
            $nameParts = explode(' ', $name, 2);
            $firstName = $nameParts[0] ?: 'Customer';
            $lastName = $nameParts[1] ?? '';
            $orderNumber = $order['order_number'] ?? ('ORD-' . $orderId);
            $total = number_format((float)($order['total'] ?? 0), 2);
            $currency = $order['currency'] ?? 'USD';

            // Check if contact exists
            $contact = CrmManager::getContactByEmail($email);

            if ($contact) {
                $contactId = (int)$contact['id'];
            } else {
                // Create new contact
                $contactId = CrmManager::createContact([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $order['customer_phone'] ?? '',
                    'source' => 'shop',
                    'status' => 'active',
                    'notes' => 'Auto-created from shop order ' . $orderNumber,
                ]);
            }

            if ($contactId < 1) {
                return;
            }

            // Add purchase activity
            CrmManager::addActivity(
                $contactId,
                'purchase',
                "Purchase: Order #{$orderNumber} — {$total} {$currency}",
                "Order placed on " . date('Y-m-d H:i', strtotime($order['created_at'] ?? 'now'))
            );

            // Calculate CLV and update tags
            $stats = self::getCustomerStats($email);
            $clv = $stats['total_spent'] ?? 0;

            // Build tags
            $existingTags = '';
            if ($contact) {
                $existingTags = $contact['tags'] ?? '';
            }
            $tags = array_filter(array_map('trim', explode(',', $existingTags)));

            // Add 'customer' tag if not present
            if (!in_array('customer', $tags)) {
                $tags[] = 'customer';
            }

            // VIP threshold from settings (default 500)
            $vipThreshold = (float)(function_exists('get_setting') ? get_setting('shop_vip_threshold', '500') : 500);
            if ($clv >= $vipThreshold && !in_array('VIP', $tags)) {
                $tags[] = 'VIP';
            }

            // Update contact with CLV info and tags
            $clvNote = "CLV: {$currency} " . number_format($clv, 2) . " | Orders: {$stats['total_orders']}";
            CrmManager::updateContact($contactId, [
                'tags' => implode(',', array_unique($tags)),
                'notes' => $clvNote,
            ]);

            if (function_exists('cms_event')) {
                cms_event('shop.crm.synced', [
                    'order_id' => $orderId,
                    'contact_id' => $contactId,
                    'clv' => $clv,
                    'is_new_contact' => !$contact,
                ]);
            }
        } catch (\Throwable $e) {
            error_log('ShopCRM::syncOrderToContact error: ' . $e->getMessage());
        }
    }

    /**
     * Get all orders for an email address
     */
    public static function getContactOrders(string $email): array
    {
        $pdo = db();
        $stmt = $pdo->prepare(
            "SELECT id, order_number, total, currency, status, payment_status, created_at
             FROM orders
             WHERE customer_email = ?
             ORDER BY created_at DESC"
        );
        $stmt->execute([trim($email)]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get customer stats for an email address
     */
    public static function getCustomerStats(string $email): array
    {
        $pdo = db();
        $email = trim($email);

        $stmt = $pdo->prepare(
            "SELECT
                COUNT(*) as total_orders,
                COALESCE(SUM(total), 0) as total_spent,
                COALESCE(AVG(total), 0) as avg_order_value,
                MIN(created_at) as first_purchase,
                MAX(created_at) as last_purchase
             FROM orders
             WHERE customer_email = ?"
        );
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'total_orders' => (int)($row['total_orders'] ?? 0),
            'total_spent' => (float)($row['total_spent'] ?? 0),
            'avg_order_value' => round((float)($row['avg_order_value'] ?? 0), 2),
            'first_purchase' => $row['first_purchase'] ?? null,
            'last_purchase' => $row['last_purchase'] ?? null,
        ];
    }
}
