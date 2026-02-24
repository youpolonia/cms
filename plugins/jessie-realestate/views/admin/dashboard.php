<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-realestate-property.php';
require_once $pluginDir . '/includes/class-realestate-agent.php';
$stats = \RealEstateProperty::getStats();
$symbol = \RealEstateProperty::getSetting('currency_symbol', '£');
$recentInquiries = db()->query("SELECT i.*, p.title AS property_title FROM re_inquiries i LEFT JOIN re_properties p ON i.property_id = p.id ORDER BY i.created_at DESC LIMIT 5")->fetchAll(\PDO::FETCH_ASSOC);
ob_start();
?>
<style>
.re-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.re-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.re-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.re-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:24px}
.re-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.re-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.re-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.re-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.re-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-re{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.inq-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.inq-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase}
.status-new{background:rgba(245,158,11,.15);color:#fbbf24}
.status-read{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-replied{background:rgba(16,185,129,.15);color:#34d399}
</style>
<div class="re-wrap">
    <div class="re-header"><h1>🏠 Real Estate Dashboard</h1><a href="/admin/realestate/properties/create" class="btn-re">➕ Add Property</a></div>
    <div class="re-stats">
        <div class="re-stat"><div class="val" style="color:#6366f1"><?= $stats['properties_active'] ?></div><div class="lbl">Active</div></div>
        <div class="re-stat"><div class="val" style="color:#f59e0b"><?= $stats['properties_pending'] ?></div><div class="lbl">Pending</div></div>
        <div class="re-stat"><div class="val" style="color:#10b981"><?= $stats['properties_sold'] ?></div><div class="lbl">Sold</div></div>
        <div class="re-stat"><div class="val" style="color:#a5b4fc"><?= $stats['agents_active'] ?></div><div class="lbl">Agents</div></div>
        <div class="re-stat"><div class="val" style="color:#ef4444"><?= $stats['inquiries_new'] ?></div><div class="lbl">New Inquiries</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/realestate/properties" class="quick-link"><span class="icon">🏘️</span><div><div class="text">Properties</div><div class="desc"><?= $stats['properties_total'] ?> total</div></div></a>
        <a href="/admin/realestate/agents" class="quick-link"><span class="icon">👤</span><div><div class="text">Agents</div><div class="desc"><?= $stats['agents_active'] ?> active</div></div></a>
        <a href="/admin/realestate/inquiries" class="quick-link"><span class="icon">📩</span><div><div class="text">Inquiries</div><div class="desc"><?= $stats['inquiries_new'] ?> new</div></div></a>
    </div>
    <?php if (!empty($recentInquiries)): ?>
    <div class="re-card">
        <h3>📩 Recent Inquiries</h3>
        <?php foreach ($recentInquiries as $inq): ?>
        <div class="inq-row">
            <div style="flex:1">
                <strong style="font-size:.85rem;color:var(--text)"><?= h($inq['name']) ?></strong>
                <span class="status-badge status-<?= h($inq['status']) ?>"><?= h($inq['status']) ?></span>
                <br><span style="font-size:.75rem;color:var(--muted)"><?= h($inq['property_title'] ?? 'Unknown') ?> — <?= h($inq['email']) ?></span>
            </div>
            <span style="font-size:.72rem;color:var(--muted)"><?= date('M j, H:i', strtotime($inq['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Real Estate Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
