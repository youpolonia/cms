<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-job-application.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \JobApplication::getAll($_GET, $page);
ob_start();
?>
<style>
.jb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.jb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.jb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.app-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;margin-bottom:12px}
.app-card .header{display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;flex-wrap:wrap;gap:8px}
.app-card h4{margin:0;font-size:.9rem;color:var(--text,#e2e8f0)}
.app-card .meta{font-size:.75rem;color:var(--muted,#94a3b8);margin-bottom:8px}
.app-card .content{font-size:.85rem;color:var(--text,#e2e8f0);line-height:1.6;max-height:60px;overflow:hidden}
.app-card .actions{display:flex;gap:8px;margin-top:12px;flex-wrap:wrap}
.app-card .actions button{padding:6px 14px;border-radius:6px;font-size:.78rem;font-weight:600;border:none;cursor:pointer}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-new{background:rgba(99,102,241,.15);color:#a5b4fc}
.status-reviewed{background:rgba(245,158,11,.15);color:#fbbf24}
.status-shortlisted{background:rgba(16,185,129,.15);color:#34d399}
.status-rejected{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
</style>
<div class="jb-wrap">
    <div class="jb-header"><h1>📋 Applications (<?= $result['total'] ?>)</h1><a href="/admin/jobs" class="btn-secondary">← Dashboard</a></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['new','reviewed','shortlisted','rejected'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search by name or email..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
    </div>
    <?php if (empty($result['applications'])): ?>
        <div style="text-align:center;padding:60px;color:var(--muted)"><p style="font-size:1.2rem">📭 No applications found</p></div>
    <?php else: foreach ($result['applications'] as $a): ?>
    <div class="app-card">
        <div class="header">
            <h4><?= h($a['applicant_name']) ?> <span style="font-size:.75rem;color:var(--muted)">→ <?= h($a['job_title'] ?? 'Unknown Job') ?></span></h4>
            <span class="status-badge status-<?= h($a['status']) ?>"><?= h($a['status']) ?></span>
        </div>
        <div class="meta">
            📧 <?= h($a['applicant_email']) ?>
            <?php if ($a['applicant_phone']): ?> · 📱 <?= h($a['applicant_phone']) ?><?php endif; ?>
            · 🏢 <?= h($a['company_name'] ?? '') ?>
            · 📅 <?= date('M j, Y H:i', strtotime($a['created_at'])) ?>
            <?php if ($a['resume_path']): ?> · <a href="<?= h($a['resume_path']) ?>" target="_blank" style="color:#a5b4fc">📎 Resume</a><?php endif; ?>
        </div>
        <?php if ($a['cover_letter']): ?>
        <div class="content"><?= nl2br(h(substr($a['cover_letter'], 0, 300))) ?><?= strlen($a['cover_letter']) > 300 ? '...' : '' ?></div>
        <?php endif; ?>
        <div class="actions">
            <button onclick="updateAppStatus(<?= $a['id'] ?>,'reviewed')" style="background:rgba(245,158,11,.15);color:#fbbf24">👁 Reviewed</button>
            <button onclick="updateAppStatus(<?= $a['id'] ?>,'shortlisted')" style="background:rgba(16,185,129,.15);color:#34d399">⭐ Shortlist</button>
            <button onclick="updateAppStatus(<?= $a['id'] ?>,'rejected')" style="background:rgba(239,68,68,.1);color:#fca5a5">✕ Reject</button>
        </div>
    </div>
    <?php endforeach; endif; ?>
    <?php if ($result['pages'] > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:16px">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <a href="?page=<?= $p ?>" style="padding:6px 12px;border-radius:6px;font-size:.82rem;border:1px solid var(--border,#334155);color:<?= $p===$page?'#fff':'var(--text)' ?>;background:<?= $p===$page?'#6366f1':'transparent' ?>;text-decoration:none"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<script>
function updateAppStatus(id,status){
    fetch('/api/jobs/application-status/'+id,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({status:status}),credentials:'same-origin'})
    .then(function(r){return r.json()}).then(function(d){if(d.ok)location.reload();else alert('Error');});
}
</script>
<?php $content = ob_get_clean(); $title = 'Applications'; require CMS_APP . '/views/admin/layouts/topbar.php';
