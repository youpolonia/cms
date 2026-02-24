<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-portfolio-project.php';
require_once $pluginDir . '/includes/class-portfolio-category.php';
require_once $pluginDir . '/includes/class-portfolio-testimonial.php';
$stats = \PortfolioProject::getStats();
$pendingTestimonials = \PortfolioTestimonial::getPending();
ob_start();
?>
<style>
.pf-wrap{max-width:1100px;margin:0 auto;padding:24px 20px}
.pf-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.pf-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.pf-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:14px;margin-bottom:24px}
.pf-stat{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:18px;text-align:center}
.pf-stat .val{font-size:1.8rem;font-weight:800;line-height:1}
.pf-stat .lbl{font-size:.72rem;color:var(--muted,#94a3b8);margin-top:4px;text-transform:uppercase;letter-spacing:.05em}
.pf-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.pf-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.btn-pf{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.quick-links{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-bottom:24px}
.quick-link{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:16px;text-decoration:none;color:var(--text,#e2e8f0);transition:all .2s;display:flex;align-items:center;gap:12px}
.quick-link:hover{border-color:#7c3aed;transform:translateY(-2px)}
.quick-link .icon{font-size:1.5rem}
.quick-link .text{font-weight:600;font-size:.9rem}
.quick-link .desc{font-size:.75rem;color:var(--muted,#94a3b8)}
.testimonial-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.testimonial-row:last-child{border-bottom:none}
.stars{color:#f59e0b;font-size:.85rem}
</style>
<div class="pf-wrap">
    <div class="pf-header"><h1>🎨 Portfolio Dashboard</h1><a href="/admin/portfolio/projects/create" class="btn-pf">➕ New Project</a></div>
    <div class="pf-stats">
        <div class="pf-stat"><div class="val" style="color:#7c3aed"><?= $stats['published'] ?></div><div class="lbl">Published</div></div>
        <div class="pf-stat"><div class="val" style="color:#f59e0b"><?= $stats['draft'] ?></div><div class="lbl">Drafts</div></div>
        <div class="pf-stat"><div class="val" style="color:#10b981"><?= $stats['featured'] ?></div><div class="lbl">Featured</div></div>
        <div class="pf-stat"><div class="val" style="color:#a5b4fc"><?= number_format($stats['total_views']) ?></div><div class="lbl">Total Views</div></div>
        <div class="pf-stat"><div class="val" style="color:#ec4899"><?= $stats['total_testimonials'] ?></div><div class="lbl">Testimonials</div></div>
    </div>
    <div class="quick-links">
        <a href="/admin/portfolio/projects" class="quick-link"><span class="icon">💼</span><div><div class="text">Projects</div><div class="desc"><?= $stats['total'] ?> total</div></div></a>
        <a href="/admin/portfolio/categories" class="quick-link"><span class="icon">📁</span><div><div class="text">Categories</div><div class="desc"><?= $stats['categories'] ?> active</div></div></a>
        <a href="/admin/portfolio/testimonials" class="quick-link"><span class="icon">💬</span><div><div class="text">Testimonials</div><div class="desc"><?= $stats['pending_testimonials'] ?> pending</div></div></a>
        <a href="/portfolio" class="quick-link" target="_blank"><span class="icon">🌐</span><div><div class="text">View Portfolio</div><div class="desc">Public page</div></div></a>
    </div>
    <?php if (!empty($pendingTestimonials)): ?>
    <div class="pf-card">
        <h3>💬 Pending Testimonials</h3>
        <?php foreach (array_slice($pendingTestimonials, 0, 5) as $t): ?>
        <div class="testimonial-row">
            <div style="flex:1">
                <strong style="font-size:.85rem;color:var(--text)"><?= h($t['client_name']) ?></strong>
                <?php if ($t['client_company']): ?><span style="font-size:.75rem;color:var(--muted)"> — <?= h($t['client_company']) ?></span><?php endif; ?>
                <br><span style="font-size:.75rem;color:var(--muted)"><?= h($t['project_title'] ?? 'General') ?> · <span class="stars"><?= str_repeat('★', (int)$t['rating']) ?></span></span>
                <br><span style="font-size:.8rem;color:var(--text);opacity:.8">"<?= h(mb_substr($t['content'], 0, 100)) ?><?= mb_strlen($t['content']) > 100 ? '…' : '' ?>"</span>
            </div>
            <div style="display:flex;gap:6px">
                <form method="post" action="/admin/portfolio/testimonials/<?= $t['id'] ?>/approve" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" style="background:rgba(16,185,129,.15);color:#34d399;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✓</button></form>
                <form method="post" action="/admin/portfolio/testimonials/<?= $t['id'] ?>/delete" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✕</button></form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); $title = 'Portfolio Dashboard'; require CMS_APP . '/views/admin/layouts/topbar.php';
