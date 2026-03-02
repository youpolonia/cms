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
require_once CMS_ROOT . '/core/payment-gateway.php';
require_once CMS_ROOT . '/plugins/shared/jessie-ical.php';

// Handle iCal export before JSON header
$path = trim(preg_replace('#^/api/events/?#', '', $uri), '/');
if ($path === 'ical') {
    // Export all upcoming events as subscribable calendar
    $events = \EventManager::getUpcoming(50);
    $icalEvents = array_map(function($e) {
        return [
            'id' => $e['id'], 'title' => $e['title'],
            'description' => $e['description'] ?? '',
            'location' => $e['location'] ?? '',
            'start' => $e['start_date'] . ' ' . ($e['start_time'] ?? '00:00'),
            'end' => ($e['end_date'] ?? $e['start_date']) . ' ' . ($e['end_time'] ?? '23:59'),
            'url' => (function_exists('get_setting') ? get_setting('site_url', '') : '') . '/events/' . $e['slug'],
        ];
    }, $events);
    JessieICal::output($icalEvents);
}
if (preg_match('#^ical/(\d+)$#', $path, $m)) {
    $event = \EventManager::get((int)$m[1]);
    if (!$event) { http_response_code(404); exit; }
    JessieICal::download([
        'id' => $event['id'], 'title' => $event['title'],
        'description' => $event['description'] ?? '',
        'location' => $event['location'] ?? '',
        'start' => $event['start_date'] . ' ' . ($event['start_time'] ?? '00:00'),
        'end' => ($event['end_date'] ?? $event['start_date']) . ' ' . ($event['end_time'] ?? '23:59'),
    ]);
}

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
                    $total = (float)($order['total'] ?? 0);
                    $payMethod = $input['payment_method'] ?? '';

                    // Free ticket or no online payment needed
                    if ($total <= 0 || !$payMethod || $payMethod === 'cash_on_delivery') {
                        if ($total <= 0) \EventOrder::updatePaymentStatus($orderId, 'paid');
                        echo json_encode(['ok' => true, 'order_id' => $orderId, 'order_number' => $order['order_number'], 'qr_code' => $order['qr_code'], 'total' => $total]);
                        exit;
                    }

                    // Online payment required
                    if (in_array($payMethod, ['stripe', 'paypal'])) {
                        $event = \EventManager::get((int)$order['event_id']);
                        $ticket = \EventTicket::get((int)$order['ticket_id']);
                        $siteUrl = rtrim(function_exists('get_setting') ? get_setting('site_url', '') : '', '/');

                        $payResult = \PaymentGateway::processPayment($payMethod, $total, [
                            'items' => [['name' => ($event['title'] ?? 'Event') . ' - ' . ($ticket['name'] ?? 'Ticket'), 'price' => $total, 'quantity' => 1]],
                            'customer_email' => $input['buyer_email'] ?? '',
                            'reference_id'   => 'event_order_' . $orderId,
                            'description'    => 'Event Ticket: ' . ($event['title'] ?? ''),
                            'metadata'       => ['order_id' => (string)$orderId, 'event_id' => (string)$order['event_id'], 'type' => 'event_ticket'],
                            'success_url'    => $siteUrl . '/events/payment-success?provider=' . $payMethod . '&order=' . $orderId . ($payMethod === 'stripe' ? '&session_id={CHECKOUT_SESSION_ID}' : ''),
                            'cancel_url'     => $siteUrl . '/events/payment-cancel?order=' . $orderId,
                        ]);

                        if (!empty($payResult['redirect'])) {
                            echo json_encode(['ok' => true, 'redirect' => $payResult['redirect'], 'order_id' => $orderId]);
                            exit;
                        } elseif (!empty($payResult['error'])) {
                            echo json_encode(['ok' => false, 'error' => 'Payment error: ' . $payResult['error']]);
                            exit;
                        }
                    }

                    // Bank transfer
                    if ($payMethod === 'bank_transfer') {
                        $instructions = \PaymentGateway::processPayment('bank_transfer', $total, [])['instructions'] ?? '';
                        echo json_encode(['ok' => true, 'order_id' => $orderId, 'order_number' => $order['order_number'], 'qr_code' => $order['qr_code'], 'total' => $total, 'instructions' => $instructions, 'pending_payment' => true]);
                        exit;
                    }

                    echo json_encode(['ok' => true, 'order_id' => $orderId, 'order_number' => $order['order_number'], 'qr_code' => $order['qr_code'], 'total' => $total]);
                } catch (\RuntimeException $e) {
                    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
                }
                exit;
            }
            break;

        case 'verify-payment':
            // Called by payment-callback.php
            if ($method === 'POST') {
                $provider = $input['provider'] ?? '';
                $orderId = (int)($input['order_id'] ?? 0);
                $verifyParams = [];
                if ($provider === 'stripe') { $verifyParams['session_id'] = $input['session_id'] ?? ''; }
                elseif ($provider === 'paypal') { $verifyParams['order_id'] = $input['token'] ?? ''; }

                if ($orderId > 0 && $provider) {
                    $result = \PaymentGateway::verifyAndComplete($provider, $verifyParams);
                    if (!empty($result['success'])) {
                        \EventOrder::updatePaymentStatus($orderId, 'paid');
                        $order = \EventOrder::get($orderId);
                        echo json_encode(['ok' => true, 'order' => $order, 'transaction_id' => $result['transaction_id'] ?? '']);
                        exit;
                    } else {
                        echo json_encode(['ok' => false, 'error' => $result['error'] ?? 'Verification failed']);
                        exit;
                    }
                }
                echo json_encode(['ok' => false, 'error' => 'Invalid parameters']);
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
