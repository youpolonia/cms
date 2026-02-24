<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-job-listing.php';
require_once $pluginDir . '/includes/class-job-application.php';
require_once $pluginDir . '/includes/class-job-company.php';
$stats = \JobListing::getStats();
$recentApps = \JobApplication::getAll(['status' => 'new'], 1, 5);
ob_start();
?>
<style>
.jb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.jb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.jb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.jb-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:24px}
.jb-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.jb-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.jb-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.jb-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.jb-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-jb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#6366f1;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.app-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.app-row:last-child{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-new{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-reviewed{background:rgba(245,158,11,.15);color:#fbbf24}
.status-shortlisted{background:rgba(16,185,129,.15);color:#34d399}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
</style>
<div class="jb-wrap">
    <div class="jb-header"><h1>💼 Job Board Dashboard</h1><a href="/admin/jobs/listings/create" class="btn-jb">➕ Post Job</a></div>
    <div class="jb-stats">
        <div class="jb-stat"><div class="val" style="color:#6366f1"><?= $stats['active'] ?></div><div class="lbl">Active Jobs</div></div>
        <div class="jb-stat"><div class="val" style="color:#f59e0b"><?= $stats['draft'] ?></div><div class="lbl">Drafts</div></div>
        <div class="jb-stat"><div class="val" style="color:#ef4444"><?= $stats['expired'] ?></div><div class="lbl">Expired</div></div>
        <div class="jb-stat"><div class="val" style="color:#10b981"><?= $stats['featured'] ?></div><div class="lbl">Featured</div></div>
        <div class="jb-stat"><div class="val" style="color:#a5b4fc"><?= $stats['new_applications'] ?></div><div class="lbl">New Apps</div></div>
        <div class="jb-stat"><div class="val" style="color:#94a3b8"><?= $stats['companies'] ?></div><div class="lbl">Companies</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/jobs/listings" class="quick-link"><span class="icon">💼</span><div><div class="text">Jobs</div><div class="desc"><?= $stats['total'] ?> total</div></div></a>
        <a href="/admin/jobs/applications" class="quick-link"><span class="icon">📋</span><div><div class="text">Applications</div><div class="desc"><?= $stats['new_applications'] ?> new</div></div></a>
        <a href="/admin/jobs/companies" class="quick-link"><span class="icon">🏢</span><div><div class="text">Companies</div><div class="desc"><?= $stats['companies'] ?> active</div></div></a>
        <a href="/jobs" class="quick-link" target="_blank"><span class="icon">🌐</span><div><div class="text">View Board</div><div class="desc">Public page</div></div></a>
    </div>
    <?php if (!empty($recentApps['applications'])): ?>
    <div class="jb-card">
        <h3>📋 New Applications</h3>
        <?php foreach ($recentApps['applications'] as $a): ?>
        <div class="app-row">
            <div style="flex:1">
                <strong style="font-size:.85rem;color:var(--text)"><?= h($a['applicant_name']) ?></strong>
                <span style="font-size:.75rem;color:var(--muted)"> — <?= h($a['job_title'] ?? 'Unknown') ?></span>
                <br><span style="font-size:.72rem;color:var(--muted)"><?= h($a['applicant_email']) ?> · <?= date('M j, Y', strtotime($a['created_at'])) ?></span>
            </div>
            <span class="status-badge status-<?= h($a['status']) ?>"><?= h($a['status']) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Job Board Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
