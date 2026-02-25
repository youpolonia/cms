<?php
/**
 * Jessie Newsletter — Subscriber Preference Center
 * URL: /newsletter/preferences?email={email}&token={token}
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-subscriber.php';
require_once CMS_ROOT . '/plugins/jessie-newsletter/includes/class-newsletter-list.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$email = $_GET['email'] ?? ''; $token = $_GET['token'] ?? '';
$subscriber = $email ? \NewsletterSubscriber::getByEmail($email) : null;
$lists = \NewsletterList::getAll('active');
$message = ''; $error = '';

// Get subscriber's current lists
$subscribedListIds = [];
if ($subscriber) {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT list_id FROM newsletter_subscribers WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $subscribedListIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $subscriber) {
    $selectedLists = $_POST['lists'] ?? [];
    $pdo = db();
    // Unsubscribe from deselected lists
    foreach ($subscribedListIds as $lid) {
        if (!in_array($lid, $selectedLists)) {
            $pdo->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ? AND list_id = ?")->execute([$email, $lid]);
        }
    }
    // Subscribe to newly selected
    foreach ($selectedLists as $lid) {
        if (!in_array($lid, $subscribedListIds)) {
            $existing = $pdo->prepare("SELECT id FROM newsletter_subscribers WHERE email = ? AND list_id = ?");
            $existing->execute([$email, (int)$lid]); $row = $existing->fetch();
            if ($row) { $pdo->prepare("UPDATE newsletter_subscribers SET status = 'active' WHERE id = ?")->execute([$row['id']]); }
            else { \NewsletterSubscriber::subscribe($email, $subscriber['name'] ?? '', [(int)$lid], 'preference_center'); }
        }
    }
    $message = 'Preferences updated!';
    // Refresh
    $stmt = $pdo->prepare("SELECT list_id FROM newsletter_subscribers WHERE email = ? AND status = 'active'");
    $stmt->execute([$email]);
    $subscribedListIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Email Preferences</title>
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--green:#22c55e}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.wrap{max-width:500px;width:100%}
.card{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:32px}
h1{font-size:1.5rem;font-weight:800;margin-bottom:8px;text-align:center}
.sub{text-align:center;color:var(--muted);margin-bottom:24px}
.list-item{display:flex;align-items:center;gap:12px;padding:12px;border:1px solid var(--border);border-radius:10px;margin-bottom:8px;cursor:pointer;transition:.2s}
.list-item:hover{border-color:var(--accent)}
.list-item input[type=checkbox]{width:20px;height:20px;accent-color:var(--accent)}
.list-item .name{font-weight:600}.list-item .desc{color:var(--muted);font-size:.8rem;margin-top:2px}
.btn{display:block;width:100%;padding:14px;background:linear-gradient(135deg,var(--accent),#6366f1);color:#fff;border:none;border-radius:10px;font-size:1rem;font-weight:600;cursor:pointer;margin-top:16px}
.msg{background:#22c55e20;border:1px solid #22c55e;color:#86efac;padding:12px;border-radius:8px;margin-bottom:16px;text-align:center}
.unsub{text-align:center;margin-top:16px;font-size:.85rem}
.unsub a{color:#ef4444;text-decoration:underline}
</style></head><body>
<div class="wrap">
<div class="card">
<h1>📧 Email Preferences</h1>
<p class="sub"><?= $subscriber ? 'Manage your subscriptions for ' . h($email) : 'Enter your email to manage preferences' ?></p>

<?php if ($message): ?><div class="msg">✅ <?= h($message) ?></div><?php endif; ?>

<?php if (!$subscriber): ?>
<form method="GET"><input type="email" name="email" placeholder="your@email.com" style="width:100%;padding:12px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text)">
<button type="submit" class="btn">→ Manage Preferences</button></form>
<?php else: ?>
<form method="POST">
<?php foreach ($lists as $list): ?>
<label class="list-item">
    <input type="checkbox" name="lists[]" value="<?= $list['id'] ?>" <?= in_array($list['id'], $subscribedListIds) ? 'checked' : '' ?>>
    <div><div class="name"><?= h($list['name']) ?></div><?php if (!empty($list['description'])): ?><div class="desc"><?= h($list['description']) ?></div><?php endif; ?></div>
</label>
<?php endforeach; ?>
<button type="submit" class="btn">💾 Save Preferences</button>
</form>
<div class="unsub"><a href="/newsletter/unsubscribe?email=<?= urlencode($email) ?>">Unsubscribe from all</a></div>
<?php endif; ?>
</div></div></body></html>
