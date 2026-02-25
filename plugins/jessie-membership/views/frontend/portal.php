<?php
/**
 * Jessie Membership — Member Portal
 * URL: /membership/portal
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-plan.php';
require_once CMS_ROOT . '/plugins/jessie-membership/includes/class-membership-member.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$member = null; $plan = null; $error = '';
$email = $_SESSION['member_email'] ?? $_GET['email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $email = trim($_POST['email'] ?? '');
    $member = \MembershipMember::getByEmail($email);
    if (!$member) { $error = 'No membership found for this email'; }
    else { $_SESSION['member_email'] = $email; }
} elseif ($email) {
    $member = \MembershipMember::getByEmail($email);
}

if ($member) {
    $plan = \MembershipPlan::get((int)$member['plan_id']);
    $pdo = db();
    $transactions = $pdo->prepare("SELECT * FROM membership_transactions WHERE member_id = ? ORDER BY created_at DESC LIMIT 10");
    $transactions->execute([$member['id']]);
    $txns = $transactions->fetchAll(\PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Member Portal</title>
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--green:#22c55e;--amber:#f59e0b}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
a{color:var(--accent);text-decoration:none}
.container{max-width:900px;margin:0 auto;padding:32px 20px}
h1{font-size:1.8rem;font-weight:800;margin-bottom:24px}
.card{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px}
.stat{text-align:center}.stat .num{font-size:1.5rem;font-weight:700;color:var(--accent)}.stat .lbl{font-size:.8rem;color:var(--muted);margin-top:4px}
.badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.8rem;font-weight:600}
.badge-active{background:#22c55e20;color:#22c55e}.badge-expired{background:#ef444420;color:#ef4444}.badge-cancelled{background:#64748b20;color:#94a3b8}
input{width:100%;padding:12px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:1rem}
.btn{padding:12px 24px;background:linear-gradient(135deg,var(--accent),#6366f1);color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer}
.error{background:#ef444420;color:#fca5a5;padding:12px;border-radius:8px;margin-bottom:16px}
table{width:100%;border-collapse:collapse;font-size:.875rem}th{text-align:left;padding:8px;color:var(--muted);font-size:.75rem;text-transform:uppercase;border-bottom:1px solid var(--border)}
td{padding:8px;border-bottom:1px solid rgba(51,65,85,.5)}
.back{display:inline-block;color:var(--accent);margin-bottom:16px;font-size:.875rem}
</style></head><body>
<div class="container">
<a href="/" class="back">← Home</a>
<h1>🎫 Member Portal</h1>

<?php if (!$member): ?>
    <?php if ($error): ?><div class="error">⚠️ <?= h($error) ?></div><?php endif; ?>
    <div class="card" style="max-width:480px;margin:0 auto">
        <h2 style="margin-bottom:16px;font-size:1.2rem">Sign In</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Your membership email" required value="<?= h($email) ?>">
            <button type="submit" class="btn" style="width:100%;margin-top:12px">→ Access Portal</button>
        </form>
        <p style="text-align:center;margin-top:16px;font-size:.85rem;color:var(--muted)">Not a member? <a href="/membership/signup">Join now</a></p>
    </div>
<?php else: ?>
    <div class="grid">
        <div class="card stat"><div class="num"><?= h($plan['name'] ?? 'N/A') ?></div><div class="lbl">Current Plan</div></div>
        <div class="card stat"><div class="num"><span class="badge badge-<?= $member['status'] ?>"><?= strtoupper($member['status']) ?></span></div><div class="lbl">Status</div></div>
        <div class="card stat"><div class="num"><?= $member['expires_at'] ? date('M j, Y', strtotime($member['expires_at'])) : '∞' ?></div><div class="lbl">Expires</div></div>
        <div class="card stat"><div class="num"><?= date('M j, Y', strtotime($member['created_at'])) ?></div><div class="lbl">Member Since</div></div>
    </div>

    <div class="card">
        <h3 style="margin-bottom:12px">📋 Your Information</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:.9rem">
            <div><span style="color:var(--muted)">Name:</span> <?= h($member['name'] ?? 'N/A') ?></div>
            <div><span style="color:var(--muted)">Email:</span> <?= h($member['email']) ?></div>
            <div><span style="color:var(--muted)">Plan:</span> <?= h($plan['name'] ?? 'N/A') ?> — <?= (float)($plan['price']??0) === 0.0 ? 'Free' : '$'.number_format((float)$plan['price'],2).'/'.h($plan['billing_period']??'month') ?></div>
            <div><span style="color:var(--muted)">Member ID:</span> #<?= $member['id'] ?></div>
        </div>
    </div>

    <?php if ($plan && !empty($plan['features'])): $features = is_array($plan['features']) ? $plan['features'] : json_decode($plan['features_json'] ?? '[]', true); ?>
    <?php if ($features): ?>
    <div class="card">
        <h3 style="margin-bottom:12px">✨ Your Benefits</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px;font-size:.9rem">
            <?php foreach ($features as $f): ?><div>✅ <?= h(is_string($f) ? $f : ($f['name'] ?? '')) ?></div><?php endforeach; ?>
        </div>
    </div>
    <?php endif; endif; ?>

    <?php if (!empty($txns)): ?>
    <div class="card">
        <h3 style="margin-bottom:12px">💳 Transaction History</h3>
        <table><thead><tr><th>Date</th><th>Type</th><th>Amount</th><th>Method</th><th>Reference</th></tr></thead><tbody>
        <?php foreach ($txns as $t): ?>
        <tr><td><?= date('M j, Y', strtotime($t['created_at'])) ?></td><td><?= h($t['type']) ?></td><td>$<?= number_format((float)$t['amount'], 2) ?></td><td><?= h($t['payment_method'] ?? '-') ?></td><td><?= h($t['reference'] ?? '-') ?></td></tr>
        <?php endforeach; ?>
        </tbody></table>
    </div>
    <?php endif; ?>

    <div style="display:flex;gap:12px;margin-top:12px">
        <a href="/membership/pricing" class="btn" style="text-decoration:none;text-align:center;display:inline-block">⬆️ Upgrade Plan</a>
        <a href="?logout=1" style="padding:12px 24px;background:var(--card);border:1px solid var(--border);border-radius:10px;color:var(--muted);text-decoration:none" onclick="delete sessionStorage['member_email']">Sign Out</a>
    </div>
<?php endif; ?>
</div></body></html>
