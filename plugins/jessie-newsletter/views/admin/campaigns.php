<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-campaign.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \NewsletterCampaign::getAll($_GET, $page);
ob_start();
?>
<style>
.nl-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.camp-grid{display:grid;gap:14px}
.camp-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;display:flex;align-items:center;gap:16px;transition:.2s}
.camp-card:hover{border-color:#6366f1}
.camp-info{flex:1;min-width:0}
.camp-info h4{margin:0 0 4px;font-size:.95rem;color:var(--text,#e2e8f0)}
.camp-info .meta{font-size:.75rem;color:var(--muted,#94a3b8)}
.camp-metrics{display:flex;gap:20px;text-align:center}
.camp-metrics .metric{min-width:50px}
.camp-metrics .metric .v{font-weight:700;font-size:.95rem;color:var(--text,#e2e8f0)}
.camp-metrics .metric .l{font-size:.65rem;color:var(--muted,#94a3b8);text-transform:uppercase}
.camp-actions{display:flex;gap:8px}
.camp-actions a,.camp-actions button{font-size:.78rem;padding:4px 10px;border-radius:6px;text-decoration:none;border:1px solid var(--border,#334155);background:none;color:var(--text,#e2e8f0);cursor:pointer}
.camp-actions a:hover,.camp-actions button:hover{border-color:#6366f1}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-draft{background:rgba(107,114,128,.15);color:#9ca3af}
.status-scheduled{background:rgba(245,158,11,.15);color:#fbbf24}
.status-sending{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-sent{background:rgba(16,185,129,.15);color:#34d399}
.status-paused{background:rgba(239,68,68,.15);color:#fca5a5}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>✉️ Campaigns</h1><div style="display:flex;gap:10px"><a href="/admin/newsletter" class="btn-secondary">← Dashboard</a><a href="/admin/newsletter/campaigns/create" class="btn-nl">✉️ New Campaign</a></div></div>
    <div class="camp-grid">
        <?php foreach ($result['campaigns'] as $c): $sent = max(1, (int)$c['stats_sent'] ?: 1); ?>
        <div class="camp-card">
            <div class="camp-info">
                <h4><?= h($c['name']) ?> <span class="status-badge status-<?= h($c['status']) ?>"><?= h($c['status']) ?></span></h4>
                <div class="meta">📋 <?= h($c['list_name'] ?? 'No list') ?> · <?= h($c['subject'] ?: 'No subject') ?> · <?= date('M j', strtotime($c['updated_at'])) ?></div>
            </div>
            <?php if ($c['status'] === 'sent'): ?>
            <div class="camp-metrics">
                <div class="metric"><div class="v"><?= $c['stats_sent'] ?></div><div class="l">Sent</div></div>
                <div class="metric"><div class="v" style="color:#10b981"><?= round($c['stats_opened']/$sent*100) ?>%</div><div class="l">Opens</div></div>
                <div class="metric"><div class="v" style="color:#6366f1"><?= round($c['stats_clicked']/$sent*100) ?>%</div><div class="l">Clicks</div></div>
            </div>
            <?php endif; ?>
            <div class="camp-actions">
                <a href="/admin/newsletter/campaigns/<?= $c['id'] ?>/edit">✏️ Edit</a>
                <?php if ($c['status'] === 'draft'): ?><button onclick="if(confirm('Send now?'))fetch('/api/newsletter/send',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({campaign_id:<?= $c['id'] ?>}),credentials:'same-origin'}).then(function(){location.reload()})">🚀 Send</button><?php endif; ?>
                <button onclick="fetch('/api/newsletter/campaigns/duplicate/<?= $c['id'] ?>',{method:'POST',credentials:'same-origin'}).then(function(){location.reload()})">📋 Duplicate</button>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($result['campaigns'])): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">No campaigns yet.</p><a href="/admin/newsletter/campaigns/create" class="btn-nl" style="margin-top:12px">✉️ Create First Campaign</a></div>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Campaigns'; require CMS_APP . '/views/admin/layouts/topbar.php';
