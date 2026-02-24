<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-subscriber.php';
require_once $pluginDir . '/includes/class-newsletter-campaign.php';
require_once $pluginDir . '/includes/class-newsletter-list.php';
$subStats = \NewsletterSubscriber::getStats();
$campStats = \NewsletterCampaign::getStats();
$lists = \NewsletterList::getAll('active');
$recentResult = \NewsletterCampaign::getAll(['status' => 'sent'], 1, 5);
ob_start();
?>
<style>
.nl-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.nl-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px}
.nl-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.nl-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.nl-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.nl-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.nl-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.camp-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.camp-row:last-child{border-bottom:none}
.camp-name{font-weight:600;font-size:.85rem;color:var(--text,#e2e8f0);flex:1}
.camp-metric{text-align:center;min-width:60px;font-size:.78rem;color:var(--muted,#94a3b8)}
.camp-metric .v{font-weight:700;font-size:.95rem;color:var(--text,#e2e8f0)}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>📧 Newsletter Dashboard</h1><a href="/admin/newsletter/campaigns/create" class="btn-nl">✉️ New Campaign</a></div>
    <div class="nl-stats">
        <div class="nl-stat"><div class="val" style="color:#6366f1"><?= $subStats['active'] ?></div><div class="lbl">Subscribers</div></div>
        <div class="nl-stat"><div class="val" style="color:#10b981">+<?= $subStats['new_30d'] ?></div><div class="lbl">New (30d)</div></div>
        <div class="nl-stat"><div class="val" style="color:#a5b4fc"><?= $campStats['sent'] ?></div><div class="lbl">Campaigns Sent</div></div>
        <div class="nl-stat"><div class="val" style="color:#f59e0b"><?= $campStats['open_rate'] ?>%</div><div class="lbl">Avg Open Rate</div></div>
        <div class="nl-stat"><div class="val" style="color:#10b981"><?= $campStats['click_rate'] ?>%</div><div class="lbl">Avg Click Rate</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/newsletter/campaigns" class="quick-link"><span class="icon">✉️</span><div><div class="text">Campaigns</div><div class="desc"><?= $campStats['total'] ?> total</div></div></a>
        <a href="/admin/newsletter/subscribers" class="quick-link"><span class="icon">👥</span><div><div class="text">Subscribers</div><div class="desc"><?= $subStats['total'] ?> total</div></div></a>
        <a href="/admin/newsletter/lists" class="quick-link"><span class="icon">📋</span><div><div class="text">Lists</div><div class="desc"><?= count($lists) ?> active</div></div></a>
        <a href="/admin/newsletter/templates" class="quick-link"><span class="icon">🎨</span><div><div class="text">Templates</div><div class="desc">Email designs</div></div></a>
    </div>
    <div class="nl-card">
        <h3>📊 Recent Campaigns</h3>
        <?php if (empty($recentResult['campaigns'])): ?>
            <p style="color:var(--muted);font-size:.85rem">No campaigns sent yet. <a href="/admin/newsletter/campaigns/create" style="color:#a5b4fc">Create your first →</a></p>
        <?php else: foreach ($recentResult['campaigns'] as $c): $sent = max(1, $c['stats_sent']); ?>
            <div class="camp-row">
                <div class="camp-name"><?= h($c['name']) ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($c['completed_at'] ? date('M j, Y', strtotime($c['completed_at'])) : '') ?> · <?= h($c['list_name'] ?? '') ?></span></div>
                <div class="camp-metric"><div class="v"><?= $c['stats_sent'] ?></div>Sent</div>
                <div class="camp-metric"><div class="v"><?= round($c['stats_opened']/$sent*100) ?>%</div>Opens</div>
                <div class="camp-metric"><div class="v"><?= round($c['stats_clicked']/$sent*100) ?>%</div>Clicks</div>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Newsletter Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
