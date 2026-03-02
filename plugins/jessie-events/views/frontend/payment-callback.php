<?php
/**
 * Events Payment Callback — Success/Cancel handlers
 * Routes: /events/payment-success, /events/payment-cancel
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-manager.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-ticket.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-order.php';
require_once CMS_ROOT . '/core/payment-gateway.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$action = $eventPaymentAction ?? '';
$orderId = (int)($_GET['order'] ?? 0);
$provider = $_GET['provider'] ?? '';
$currency = function_exists('get_setting') ? get_setting('payment_currency', 'USD') : 'USD';
$sym = function_exists('get_setting') ? get_setting('currency_symbol', '$') : '$';

$order = null;
$event = null;
$ticket = null;
$error = '';
$success = false;

if ($orderId > 0) {
    $order = EventOrder::get($orderId);
    if ($order) {
        $event = EventManager::get((int)$order['event_id']);
        $ticket = EventTicket::get((int)$order['ticket_id']);
    }
}

// ─── SUCCESS ───
if ($action === 'success' && $order) {
    $verifyParams = [];
    if ($provider === 'stripe') { $verifyParams['session_id'] = $_GET['session_id'] ?? ''; }
    elseif ($provider === 'paypal') { $verifyParams['order_id'] = $_GET['token'] ?? ''; }

    $result = PaymentGateway::verifyAndComplete($provider, $verifyParams);
    if (!empty($result['success'])) {
        EventOrder::updatePaymentStatus($orderId, 'paid');
        $order = EventOrder::get($orderId); // refresh
        $success = true;
    } else {
        $error = $result['error'] ?? 'Payment verification failed';
    }
}

// ─── CANCEL ───
if ($action === 'cancel') {
    $error = 'Payment was cancelled. Your order has not been confirmed.';
}

$siteTitle = '';
try { $stmt = db()->query("SELECT value FROM settings WHERE `key` = 'site_title' LIMIT 1"); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $success ? 'Payment Successful' : 'Payment Status' ?> — <?= h($siteTitle ?: 'Events') ?></title>
    <style>
        :root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--green:#10b981;--danger:#ef4444;--warning:#f59e0b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .container{max-width:500px;width:100%;text-align:center}
        .icon{font-size:4rem;margin-bottom:20px}
        h1{font-size:1.6rem;margin-bottom:12px}
        p{color:var(--muted);margin-bottom:24px}
        .card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:24px;text-align:left;margin-bottom:24px}
        .card h3{font-size:.85rem;text-transform:uppercase;color:var(--muted);margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .row{display:flex;justify-content:space-between;margin-bottom:10px;font-size:.9rem}
        .row .label{color:var(--muted)}.row .value{font-weight:600}
        .qr-box{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:16px;margin-top:16px;text-align:center}
        .qr-box .code{font-family:monospace;font-size:1.1rem;color:var(--accent);word-break:break-all}
        .qr-box .hint{font-size:.75rem;color:var(--muted);margin-top:8px}
        .btn{display:inline-block;padding:14px 28px;background:linear-gradient(135deg,var(--accent),#8b5cf6);color:#fff;border:none;border-radius:10px;font-weight:700;font-size:1rem;text-decoration:none;cursor:pointer}
        .btn:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(99,102,241,.3)}
        .btn-ghost{background:transparent;border:1px solid var(--border);color:var(--text);margin-right:12px}
        .btn-ghost:hover{background:var(--card)}
        .error-box{background:rgba(239,68,68,.1);border:1px solid var(--danger);color:#fca5a5;padding:16px;border-radius:12px;margin-bottom:24px}
        .warning-box{background:rgba(245,158,11,.1);border:1px solid var(--warning);color:#fcd34d;padding:16px;border-radius:12px;margin-bottom:24px}
        .success-badge{background:var(--green);color:#fff;padding:4px 12px;border-radius:20px;font-size:.75rem;font-weight:700;display:inline-block;margin-bottom:8px}
    </style>
</head>
<body>
<div class="container">
    <?php if ($success && $order): ?>
        <div class="icon">🎉</div>
        <h1>Payment Successful!</h1>
        <p>Your ticket purchase has been confirmed.</p>

        <div class="card">
            <h3>🎫 Order Details</h3>
            <div class="row"><span class="label">Order #</span><span class="value"><?= h($order['order_number']) ?></span></div>
            <div class="row"><span class="label">Event</span><span class="value"><?= h($event['title'] ?? 'N/A') ?></span></div>
            <div class="row"><span class="label">Ticket</span><span class="value"><?= h($ticket['name'] ?? 'N/A') ?> × <?= (int)$order['quantity'] ?></span></div>
            <div class="row"><span class="label">Date</span><span class="value"><?= date('M j, Y', strtotime($event['start_date'] ?? 'now')) ?></span></div>
            <div class="row"><span class="label">Total Paid</span><span class="value" style="color:var(--green)"><?= $sym ?><?= number_format((float)$order['total'], 2) ?></span></div>
            <div class="row"><span class="label">Status</span><span class="value"><span class="success-badge">✓ PAID</span></span></div>

            <div class="qr-box">
                <div style="font-size:.8rem;color:var(--muted);margin-bottom:8px">Your Check-in Code</div>
                <div class="code"><?= h($order['qr_code']) ?></div>
                <div class="hint">Show this code at the event entrance</div>
            </div>
        </div>

        <a href="/events" class="btn">← Browse More Events</a>

    <?php elseif ($error): ?>
        <?php if ($action === 'cancel'): ?>
        <div class="icon">⚠️</div>
        <div class="warning-box"><?= h($error) ?></div>
        <?php else: ?>
        <div class="icon">❌</div>
        <div class="error-box"><?= h($error) ?></div>
        <?php endif; ?>

        <h1><?= $action === 'cancel' ? 'Payment Cancelled' : 'Payment Failed' ?></h1>
        <p>Your order was not completed. No charges were made.</p>

        <?php if ($event): ?>
        <a href="/events/<?= h($event['slug'] ?? '') ?>" class="btn btn-ghost">← Back to Event</a>
        <?php endif; ?>
        <a href="/events" class="btn">Browse Events</a>

    <?php else: ?>
        <div class="icon">❓</div>
        <h1>Invalid Request</h1>
        <p>We couldn't find your order. Please try again.</p>
        <a href="/events" class="btn">Browse Events</a>
    <?php endif; ?>
</div>
</body>
</html>
