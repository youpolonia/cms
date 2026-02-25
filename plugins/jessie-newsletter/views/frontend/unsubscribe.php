<?php
/**
 * Jessie Newsletter — Unsubscribe Page
 * URL: /newsletter/unsubscribe?email={email}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-subscriber.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$email = $_GET['email'] ?? '';
$done = false;

if ($email && ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['confirm']))) {
    $result = \NewsletterSubscriber::unsubscribe($email);
    $done = true;
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Unsubscribe</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:#1e293b;border:1px solid #334155;border-radius:16px;padding:40px;max-width:480px;text-align:center}
h1{margin-bottom:12px}p{color:#94a3b8;margin-bottom:24px}
.btn{padding:12px 32px;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;margin:4px}
.btn-danger{background:#ef4444;color:#fff}.btn-back{background:#334155;color:#e2e8f0}
a{color:#8b5cf6;text-decoration:none}
</style></head><body>
<div class="card">
<?php if ($done): ?>
<h1>👋 Unsubscribed</h1>
<p>You've been unsubscribed from all lists. Sorry to see you go!</p>
<a href="/newsletter/preferences?email=<?= urlencode($email) ?>">Changed your mind? Re-subscribe →</a>
<?php else: ?>
<h1>😢 Unsubscribe</h1>
<p>Are you sure you want to unsubscribe <strong><?= h($email) ?></strong> from all emails?</p>
<form method="POST"><button type="submit" class="btn btn-danger">Yes, Unsubscribe Me</button></form>
<a href="/newsletter/preferences?email=<?= urlencode($email) ?>" class="btn btn-back" style="display:inline-block;margin-top:12px;text-decoration:none">Manage Preferences Instead</a>
<?php endif; ?>
</div></body></html>
