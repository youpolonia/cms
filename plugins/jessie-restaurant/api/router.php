<?php
/**
 * Jessie Restaurant — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-restaurant-menu.php';
require_once $pluginDir . '/includes/class-restaurant-order.php';
require_once $pluginDir . '/includes/class-restaurant-ai.php';

header('Content-Type: application/json');
$isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/restaurant/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC ───
        case 'menu':
            echo json_encode(['ok' => true, 'menu' => \RestaurantMenu::getFullMenu()]);
            exit;

        case 'item':
            if ($id) {
                $item = \RestaurantMenu::getItem($id);
                echo json_encode(['ok' => (bool)$item, 'item' => $item]);
                exit;
            }
            break;

        case 'categories':
            echo json_encode(['ok' => true, 'categories' => \RestaurantMenu::getCategories('active')]);
            exit;

        case 'order':
            if ($method === 'POST') {
                // Validate
                if (empty($input['customer_name']) || empty($input['customer_phone']) || empty($input['items'])) {
                    echo json_encode(['ok' => false, 'error' => 'Name, phone and items required']);
                    exit;
                }
                // Check if accepting orders
                if (!\RestaurantMenu::getSetting('accept_orders', '1')) {
                    echo json_encode(['ok' => false, 'error' => 'Not accepting orders right now']);
                    exit;
                }
                // Check min order
                $minOrder = (float)\RestaurantMenu::getSetting('min_order_amount', '0');
                if ($minOrder > 0) {
                    $subtotal = 0;
                    foreach ($input['items'] as $i) {
                        $mi = \RestaurantMenu::getItem((int)$i['id']);
                        if ($mi) $subtotal += (float)($mi['sale_price'] ?: $mi['price']) * (int)($i['quantity'] ?? 1);
                    }
                    if ($subtotal < $minOrder) {
                        echo json_encode(['ok' => false, 'error' => "Minimum order is " . \RestaurantMenu::getSetting('currency_symbol', '£') . number_format($minOrder, 2)]);
                        exit;
                    }
                }
                $orderId = \RestaurantOrder::create($input);
                $order = \RestaurantOrder::get($orderId);
                echo json_encode(['ok' => true, 'order_id' => $orderId, 'order_number' => $order['order_number'], 'total' => $order['total'], 'estimated_time' => $order['estimated_time']]);
                exit;
            }
            // GET order by id
            if ($id) {
                $order = \RestaurantOrder::get($id);
                echo json_encode(['ok' => (bool)$order, 'order' => $order ? ['order_number' => $order['order_number'], 'status' => $order['status'], 'total' => $order['total'], 'estimated_time' => $order['estimated_time'], 'items' => $order['items_json']] : null]);
                exit;
            }
            break;

        case 'order-status':
            if ($id) {
                $order = \RestaurantOrder::get($id);
                echo json_encode(['ok' => (bool)$order, 'status' => $order['status'] ?? null, 'order_number' => $order['order_number'] ?? null]);
                exit;
            }
            break;

        case 'settings':
            echo json_encode(['ok' => true, 'settings' => [
                'restaurant_name' => \RestaurantMenu::getSetting('restaurant_name'),
                'currency_symbol' => \RestaurantMenu::getSetting('currency_symbol', '£'),
                'min_order_amount' => (float)\RestaurantMenu::getSetting('min_order_amount', '0'),
                'delivery_fee' => (float)\RestaurantMenu::getSetting('delivery_fee', '0'),
                'order_types' => explode(',', \RestaurantMenu::getSetting('order_types', 'delivery,pickup')),
                'opening_hours' => json_decode(\RestaurantMenu::getSetting('opening_hours', '{}'), true),
                'accept_orders' => (bool)\RestaurantMenu::getSetting('accept_orders', '1'),
                'estimated_delivery_time' => \RestaurantMenu::getSetting('estimated_delivery_time', '30-45'),
                'estimated_pickup_time' => \RestaurantMenu::getSetting('estimated_pickup_time', '15-20'),
            ]]);
            exit;

        // ─── ADMIN ───
        case 'orders':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true] + \RestaurantOrder::getAll($_GET));
            exit;

        case 'active-orders':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'orders' => \RestaurantOrder::getActiveOrders()]);
            exit;

        case 'update-status':
            if (!$isAdmin || !$id) break;
            \RestaurantOrder::updateStatus($id, $input['status'] ?? '');
            echo json_encode(['ok' => true]); exit;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \RestaurantMenu::getStats()]);
            exit;

        // ─── AI ───
        case 'ai-description':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\RestaurantAI::generateDescription($input['name'] ?? '', $input['category'] ?? '', $input['cuisine'] ?? ''));
            exit;

        case 'ai-menu':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\RestaurantAI::generateMenu($input['restaurant_name'] ?? '', $input['cuisine'] ?? '', (int)($input['count'] ?? 10)));
            exit;

        case 'ai-pricing':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(\RestaurantAI::suggestPricing($input['name'] ?? '', $input['category'] ?? '', $input['city'] ?? ''));
            exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
