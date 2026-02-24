<?php
/**
 * Jessie Affiliate — Affiliate Dashboard (public, cookie-based)
 * URL: /affiliate/dashboard
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate-program.php';
require_once CMS_ROOT . '/plugins/jessie-affiliate/includes/class-affiliate.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

// Login via cookie or email param
$affiliateEmail = $_COOKIE['aff_session'] ?? '';
if (!$affiliateEmail && !empty($_GET['email'])) {
    $affiliateEmail = trim($_GET['email']);
}
if (!$affiliateEmail && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['email'])) {
    $affiliateEmail = trim($_POST['email']);
    if ($affiliateEmail) {
        setcookie('aff_session', $affiliateEmail, time() + (365 * 86400), '/', '', false, true);
    }
}

$affiliate = $affiliateEmail ? Affiliate::getByEmail($affiliateEmail) : null;

// Get conversions and payouts for this affiliate
$conversions = [];
$payouts = [];
$program = null;
if ($affiliate) {
    $conversions = Affiliate::getConversions(['affiliate_id' => $affiliate['id']], 1, 20);
    $payouts = Affiliate::getPayouts(['affiliate_id' => $affiliate['id']], 1, 20);
    $program = AffiliateProgram::get((int)$affiliate['program_id']);
}

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Affiliate'; } catch (\Exception $e) { $siteTitle = 'Affiliate'; }
$baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Dashboard — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#7c3aed;--accent2:#a855f7}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}a:hover{color:var(--accent2)}

        .dash-header{background:linear-gradient(135deg,rgba(124,58,237,.15) 0%,rgba(168,85,247,.1) 100%);border-bottom:1px solid var(--border);padding:32px 20px}
        .dash-header-inner{max-width:1000px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px}
        .dash-header h1{font-size:1.5rem;font-weight:800}
        .dash-header .code{font-family:monospace;background:rgba(124,58,237,.2);color:#c4b5fd;padding:6px 16px;border-radius:8px;font-size:1.1rem;letter-spacing:2px;font-weight:700}

        .container{max-width:1000px;margin:0 auto;padding:24px 20px}

        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
        .stat{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;text-align:center}
        .stat .val{font-size:1.8rem;font-weight:800;line-height:1}
        .stat .lbl{font-size:.72rem;color:var(--muted);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}

        .ref-link{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:24px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .ref-link label{font-size:.82rem;font-weight:600;white-space:nowrap}
        .ref-link input{flex:1;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:10px 14px;border-radius:8px;font-size:.85rem;min-width:200px}
        .ref-link button{background:var(--accent);color:#fff;border:none;padding:10px 18px;border-radius:8px;cursor:pointer;font-size:.82rem;font-weight:600;white-space:nowrap}

        .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;margin-bottom:20px}
        .card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border)}

        .table{width:100%;border-collapse:collapse}
        .table th{font-size:.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;padding:8px 10px;text-align:left;border-bottom:1px solid var(--border)}
        .table td{padding:8px 10px;font-size:.82rem;border-bottom:1px solid rgba(51,65,85,.4)}
        .table tr:last-child td{border-bottom:none}

        .status-badge{padding:2px 8px;border-radius:4px;font-size:.68rem;font-weight:600;text-transform:uppercase}
        .status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
        .status-approved{background:rgba(16,185,129,.15);color:#34d399}
        .status-active{background:rgba(16,185,129,.15);color:#34d399}
        .status-paid{background:rgba(124,58,237,.15);color:#c4b5fd}
        .status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
        .status-completed{background:rgba(16,185,129,.15);color:#34d399}
        .status-failed{background:rgba(239,68,68,.15);color:#fca5a5}

        .login-box{max-width:400px;margin:60px auto;background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:32px;text-align:center}
        .login-box h2{font-size:1.3rem;margin-bottom:16px}
        .login-box input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:.9rem;margin-bottom:12px}
        .login-box button{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;width:100%;font-size:.9rem}

        .pending-notice{background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.3);border-radius:12px;padding:20px;text-align:center;margin-bottom:24px;color:#fbbf24}
    </style>
</head>
<body>
    <?php if (!$affiliate): ?>
    <div class="container">
        <div class="login-box">
            <h2>🔐 Affiliate Login</h2>
            <p style="color:var(--muted);font-size:.85rem;margin-bottom:20px">Enter your registered email to access your dashboard</p>
            <form method="POST" action="/affiliate/dashboard">
                <input type="email" name="email" required placeholder="your@email.com">
                <button type="submit">Access Dashboard</button>
            </form>
            <p style="margin-top:16px;font-size:.82rem;color:var(--muted)">Don't have an account? <a href="/affiliate/register">Register here</a></p>
        </div>
    </div>
    <?php else: ?>

    <div class="dash-header">
        <div class="dash-header-inner">
            <div>
                <h1>👋 Welcome, <?= h($affiliate['name']) ?></h1>
                <span style="font-size:.82rem;color:var(--muted)"><?= h($program['name'] ?? 'Affiliate Program') ?></span>
            </div>
            <div class="code"><?= h($affiliate['referral_code']) ?></div>
        </div>
    </div>

    <div class="container">
        <?php if ($affiliate['status'] === 'pending'): ?>
        <div class="pending-notice">⏳ Your application is pending approval. You'll be able to start referring once approved.</div>
        <?php endif; ?>

        <?php if ($affiliate['status'] === 'suspended'): ?>
        <div class="pending-notice" style="color:#fca5a5;border-color:rgba(239,68,68,.3);background:rgba(239,68,68,.1)">⚠️ Your account has been suspended. Please contact support for more information.</div>
        <?php endif; ?>

        <div class="stats">
            <div class="stat"><div class="val" style="color:#a5b4fc"><?= number_format((int)$affiliate['total_clicks']) ?></div><div class="lbl">Total Clicks</div></div>
            <div class="stat"><div class="val" style="color:#34d399"><?= number_format((int)$affiliate['total_conversions']) ?></div><div class="lbl">Conversions</div></div>
            <div class="stat"><div class="val" style="color:#fbbf24">$<?= number_format((float)$affiliate['total_earnings'], 2) ?></div><div class="lbl">Total Earnings</div></div>
            <div class="stat"><div class="val" style="color:#f87171">$<?= number_format((float)$affiliate['pending_payout'], 2) ?></div><div class="lbl">Pending Payout</div></div>
            <div class="stat">
                <div class="val" style="color:#818cf8"><?= (int)$affiliate['total_clicks'] > 0 ? number_format(((int)$affiliate['total_conversions'] / (int)$affiliate['total_clicks']) * 100, 1) : '0' ?>%</div>
                <div class="lbl">Conv. Rate</div>
            </div>
        </div>

        <div class="ref-link">
            <label>🔗 Your Referral Link:</label>
            <input type="text" id="refLink" value="<?= h($baseUrl) ?>/?ref=<?= h($affiliate['referral_code']) ?>" readonly onclick="this.select()">
            <button onclick="navigator.clipboard.writeText(document.getElementById('refLink').value);this.textContent='✓ Copied!';setTimeout(()=>this.textContent='📋 Copy',2000)">📋 Copy</button>
        </div>

        <?php if ($program): ?>
        <div class="card">
            <h3>📋 Program Details</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px;font-size:.85rem">
                <div><span style="color:var(--muted)">Commission:</span><br><strong><?= $program['commission_type'] === 'percentage' ? h($program['commission_value']) . '%' : '$' . number_format((float)$program['commission_value'], 2) ?></strong> <span style="font-size:.72rem;color:var(--muted)">(<?= h($program['commission_type']) ?>)</span></div>
                <div><span style="color:var(--muted)">Cookie Duration:</span><br><strong><?= (int)$program['cookie_days'] ?> days</strong></div>
                <div><span style="color:var(--muted)">Min Payout:</span><br><strong>$<?= number_format((float)$program['min_payout'], 2) ?></strong></div>
                <div><span style="color:var(--muted)">Status:</span><br><span class="status-badge status-<?= h($affiliate['status']) ?>"><?= h($affiliate['status']) ?></span></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($conversions['conversions'])): ?>
        <div class="card">
            <h3>🎯 Recent Conversions</h3>
            <table class="table"><thead><tr><th>Order</th><th>Amount</th><th>Commission</th><th>Status</th><th>Date</th></tr></thead><tbody>
                <?php foreach ($conversions['conversions'] as $c): ?>
                <tr>
                    <td style="font-family:monospace"><?= h($c['order_id'] ?: '—') ?></td>
                    <td>$<?= number_format((float)$c['order_total'], 2) ?></td>
                    <td style="font-weight:700;color:#34d399">$<?= number_format((float)$c['commission'], 2) ?></td>
                    <td><span class="status-badge status-<?= h($c['status']) ?>"><?= h($c['status']) ?></span></td>
                    <td style="font-size:.75rem;color:var(--muted)"><?= date('M j, Y', strtotime($c['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php endif; ?>

        <?php if (!empty($payouts['payouts'])): ?>
        <div class="card">
            <h3>💰 Payout History</h3>
            <table class="table"><thead><tr><th>Amount</th><th>Method</th><th>Reference</th><th>Status</th><th>Date</th></tr></thead><tbody>
                <?php foreach ($payouts['payouts'] as $po): ?>
                <tr>
                    <td style="font-weight:700;color:#34d399">$<?= number_format((float)$po['amount'], 2) ?></td>
                    <td><?= h($po['payment_method'] ?: '—') ?></td>
                    <td style="font-family:monospace;font-size:.78rem"><?= h($po['payment_reference'] ?: '—') ?></td>
                    <td><span class="status-badge status-<?= h($po['status']) ?>"><?= h($po['status']) ?></span></td>
                    <td style="font-size:.75rem;color:var(--muted)"><?= date('M j, Y', strtotime($po['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</body>
</html>
