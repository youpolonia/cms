<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-job-listing.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \JobListing::getAll($_GET, $page);
ob_start();
?>
<style>
.jb-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.jb-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.jb-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-jb{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.jb-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.jb-table th{background:rgba(99,102,241,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.jb-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.jb-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-active{background:rgba(16,185,129,.15);color:#34d399}
.status-draft{background:rgba(245,158,11,.15);color:#fbbf24}
.status-expired{background:rgba(239,68,68,.15);color:#fca5a5}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.type-badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:600;text-transform:uppercase;background:rgba(99,102,241,.1);color:#a5b4fc}
</style>
<div class="jb-wrap">
    <div class="jb-header"><h1>💼 Job Listings</h1><div style="display:flex;gap:10px"><a href="/admin/jobs" class="btn-secondary">← Dashboard</a><a href="/admin/jobs/listings/create" class="btn-jb">➕ Post Job</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['active','draft','expired'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?job_type='+this.value"><option value="">All Types</option><?php foreach (['full-time','part-time','contract','freelance'] as $t): ?><option value="<?= $t ?>" <?= ($_GET['job_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?remote_type='+this.value"><option value="">All Locations</option><?php foreach (['onsite','remote','hybrid'] as $rt): ?><option value="<?= $rt ?>" <?= ($_GET['remote_type']??'')===$rt?'selected':'' ?>><?= ucfirst($rt) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> jobs</span>
    </div>
    <table class="jb-table"><thead><tr><th>Job</th><th>Company</th><th>Type</th><th>Location</th><th>Salary</th><th>Views</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['listings'] as $j): ?>
        <tr>
            <td><strong><?= h($j['title']) ?></strong><?= $j['is_featured']?'<span style="font-size:.65rem;color:#f59e0b;margin-left:6px">⭐ FEATURED</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($j['category']?:'-') ?> · <?= h($j['experience_level']) ?></span></td>
            <td style="font-size:.82rem"><?= h($j['company_name']?:'—') ?></td>
            <td><span class="type-badge"><?= h($j['job_type']) ?></span><br><span style="font-size:.65rem;color:var(--muted)"><?= h($j['remote_type']) ?></span></td>
            <td style="font-size:.82rem"><?= h($j['location']?:'—') ?></td>
            <td style="font-size:.82rem"><?php if ($j['salary_min'] || $j['salary_max']): ?><?= $j['salary_currency'] ?> <?= $j['salary_min']?number_format((float)$j['salary_min']):'' ?><?= ($j['salary_min']&&$j['salary_max'])?'–':'' ?><?= $j['salary_max']?number_format((float)$j['salary_max']):'' ?><?php else: ?>—<?php endif; ?></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= number_format($j['view_count']) ?></td>
            <td><span class="status-badge status-<?= h($j['status']) ?>"><?= h($j['status']) ?></span></td>
            <td><a href="/admin/jobs/listings/<?= $j['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['listings'])): ?><tr><td colspan="8" style="text-align:center;padding:40px;color:var(--muted)">No jobs found.</td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div style="display:flex;justify-content:center;gap:6px;margin-top:16px">
        <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
        <a href="?page=<?= $p ?>" style="padding:6px 12px;border-radius:6px;font-size:.82rem;border:1px solid var(--border,#334155);color:<?= $p===$page?'#fff':'var(--text)' ?>;background:<?= $p===$page?'#6366f1':'transparent' ?>;text-decoration:none"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Job Listings'; require CMS_APP . '/views/admin/layouts/topbar.php';
