<?php
/**
 * Jessie Events — API Router
 */
defined('CMS_ROOT') or die('Direct access not allowed');
$pluginDir = __DIR__ . '/..';
$method = $_SERVER['REQUEST_METHOD'];
$uri = strtok($_SERVER['REQUEST_URI'] ?? '', '?');

require_once $pluginDir . '/includes/class-event-manager.php';
require_once $pluginDir . '/includes/class-event-ticket.php';
require_once $pluginDir . '/includes/class-event-order.php';

header('Content-Type: application/json');
$isAdmin = isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'admin';
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (preg_match('#^/api/events/([\w-]+)(?:/(\d+))?$#', $uri, $m)) {
    $endpoint = $m[1];
    $id = isset($m[2]) ? (int)$m[2] : null;

    switch ($endpoint) {
        // ─── PUBLIC ───
        case 'events':
            $page = max(1, (int)($_GET['page'] ?? 1));
            $filters = [
                'status'    => ($_GET['status'] ?? null) ?: null,
                'category'  => ($_GET['category'] ?? null) ?: null,
                'city'      => ($_GET['city'] ?? null) ?: null,
                'search'    => ($_GET['q'] ?? null) ?: null,
                'date_from' => ($_GET['date_from'] ?? null) ?: null,
                'date_to'   => ($_GET['date_to'] ?? null) ?: null,
                'month'     => ($_GET['month'] ?? null) ?: null,
                'sort'      => ($_GET['sort'] ?? null) ?: null,
            ];
            $result = \EventManager::getAll(array_filter($filters), $page, (int)($_GET['per_page'] ?? 12));
            echo json_encode(['ok' => true] + $result);
            exit;

        case 'event':
            if ($id) {
                $event = \EventManager::get($id);
                echo json_encode(['ok' => (bool)$event, 'event' => $event]);
                exit;
            }
            break;

        case 'tickets':
            if ($id) {
                $tickets = \EventTicket::getAvailable($id);
                echo json_encode(['ok' => true, 'tickets' => $tickets]);
                exit;
            }
            break;

        case 'purchase':
            if ($method === 'POST') {
                if (empty($input['buyer_name']) || empty($input['ticket_id']) || empty($input['event_id'])) {
                    echo json_encode(['ok' => false, 'error' => 'Name, ticket_id and event_id required']);
                    exit;
                }
                try {
                    $orderId = \EventOrder::create($input);
                    $order = \EventOrder::get($orderId);
                    echo json_encode(['ok' => true, 'order_id' => $orderId, 'order_number' => $order['order_number'], 'qr_code' => $order['qr_code'], 'total' => $order['total']]);
                } catch (\RuntimeException $e) {
                    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                }
                exit;
            }
            break;

        // ─── ADMIN ───
        case 'check-in':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            if ($method === 'POST') {
                $qr = ($input['qr_code'] ?? null) ?: '';
                echo json_encode(\EventOrder::checkIn($qr));
                exit;
            }
            break;

        case 'stats':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true, 'stats' => \EventManager::getStats()]);
            exit;

        case 'ai-description':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            if (!function_exists('ai_universal_generate')) require_once CMS_ROOT . '/core/ai_content.php';
            $title = ($input['title'] ?? null) ?: '';
            $category = ($input['category'] ?? null) ?: '';
            $venue = ($input['venue'] ?? null) ?: '';
            $prompt = "Write an engaging event description.\n\nEvent: {$title}\n"
                . ($category ? "Category: {$category}\n" : '') . ($venue ? "Venue: {$venue}\n" : '')
                . "\nReturn JSON: {\"description\": \"2-3 paragraphs, engaging\", \"short_description\": \"1-2 sentences\"}\nReturn ONLY valid JSON.";
            $response = ai_universal_generate($prompt, ['max_tokens' => 600, 'temperature' => 0.6]);
            $response = trim($response);
            if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/', $response, $rm)) $response = $rm[1];
            $data = json_decode($response, true);
            if (!$data && preg_match('/[\[{][\s\S]*[\]}]/', $response, $rm)) $data = json_decode($rm[0], true);
            echo json_encode($data ? ['ok' => true, 'data' => $data] : ['ok' => false, 'error' => 'AI generation failed']);
            exit;

        case 'orders':
            if (!$isAdmin) { http_response_code(403); echo json_encode(['ok' => false]); exit; }
            echo json_encode(['ok' => true] + \EventOrder::getAll($_GET));
            exit;

        case 'update-payment':
            if (!$isAdmin || !$id) break;
            \EventOrder::updatePaymentStatus($id, ($input['status'] ?? null) ?: '');
            echo json_encode(['ok' => true]); exit;
    }
}

echo json_encode(['ok' => false, 'error' => 'Unknown endpoint']);
