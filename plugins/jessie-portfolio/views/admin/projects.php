<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-portfolio-project.php';
require_once $pluginDir . '/includes/class-portfolio-category.php';
$page = max(1, (int)($_GET['page'] ?? 1));
$result = \PortfolioProject::getAll($_GET, $page);
$categories = \PortfolioCategory::getAll('active');
ob_start();
?>
<style>
.pf-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.pf-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.pf-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-pf{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.pf-table{width:100%;border-collapse:separate;border-spacing:0;background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden}
.pf-table th{background:rgba(124,58,237,.08);color:var(--muted,#94a3b8);font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;padding:12px 14px;text-align:left;border-bottom:1px solid var(--border,#334155)}
.pf-table td{padding:10px 14px;border-bottom:1px solid rgba(51,65,85,.5);font-size:.85rem;color:var(--text,#e2e8f0)}
.pf-table tr:last-child td{border-bottom:none}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-published{background:rgba(16,185,129,.15);color:#34d399}
.status-draft{background:rgba(245,158,11,.15);color:#fbbf24}
.filter-bar{display:flex;gap:10px;margin-bottom:16px;flex-wrap:wrap}
.filter-bar select,.filter-bar input{background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 12px;border-radius:8px;font-size:.82rem}
.thumb{width:40px;height:40px;border-radius:6px;object-fit:cover;background:var(--border,#334155)}
.pf-pager{display:flex;gap:8px;margin-top:16px;justify-content:center}
.pf-pager a{padding:6px 12px;border-radius:6px;font-size:.82rem;text-decoration:none;border:1px solid var(--border,#334155);color:var(--text,#e2e8f0)}
.pf-pager a.active{background:#7c3aed;color:#fff;border-color:#7c3aed}
</style>
<div class="pf-wrap">
    <div class="pf-header"><h1>💼 Projects</h1><div style="display:flex;gap:10px"><a href="/admin/portfolio" class="btn-secondary">← Dashboard</a><a href="/admin/portfolio/projects/create" class="btn-pf">➕ Add</a></div></div>
    <div class="filter-bar">
        <select onchange="location.href='?status='+this.value"><option value="">All Status</option><?php foreach (['published','draft'] as $s): ?><option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
        <select onchange="location.href='?category_id='+this.value"><option value="">All Categories</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= ($_GET['category_id']??'')==(string)$c['id']?'selected':'' ?>><?= h($c['name']) ?></option><?php endforeach; ?></select>
        <input type="text" placeholder="Search..." value="<?= h($_GET['search']??'') ?>" onchange="location.href='?search='+encodeURIComponent(this.value)">
        <span style="color:var(--muted);font-size:.82rem;padding:8px"><?= $result['total'] ?> projects</span>
    </div>
    <table class="pf-table"><thead><tr><th></th><th>Project</th><th>Category</th><th>Client</th><th>Views</th><th>Status</th><th></th></tr></thead><tbody>
        <?php foreach ($result['projects'] as $p): ?>
        <tr>
            <td><?php if ($p['cover_image']): ?><img src="<?= h($p['cover_image']) ?>" class="thumb" alt=""><?php else: ?><div class="thumb" style="display:flex;align-items:center;justify-content:center;font-size:1.1rem">💼</div><?php endif; ?></td>
            <td><strong><?= h($p['title']) ?></strong><?= $p['is_featured']?'<span style="font-size:.65rem;color:#f59e0b;margin-left:6px">⭐ FEATURED</span>':'' ?><br><span style="font-size:.72rem;color:var(--muted)"><?= h($p['short_description'] ?: '—') ?></span></td>
            <td style="font-size:.82rem"><?= h($p['category_name'] ?? '—') ?></td>
            <td style="font-size:.82rem"><?= h($p['client_name'] ?: '—') ?></td>
            <td style="font-size:.82rem;color:var(--muted)"><?= number_format($p['view_count']) ?></td>
            <td><span class="status-badge status-<?= h($p['status']) ?>"><?= h($p['status']) ?></span></td>
            <td><a href="/admin/portfolio/projects/<?= $p['id'] ?>/edit" style="color:#a5b4fc;font-size:.78rem;text-decoration:none">✏️</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($result['projects'])): ?><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--muted)">No projects found.</td></tr><?php endif; ?>
    </tbody></table>
    <?php if ($result['pages'] > 1): ?>
    <div class="pf-pager">
        <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
        <a href="?page=<?= $i ?>" class="<?= $i === $result['page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Portfolio Projects'; require CMS_APP . '/views/admin/layouts/topbar.php';
