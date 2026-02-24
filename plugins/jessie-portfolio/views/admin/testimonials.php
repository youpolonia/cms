<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-portfolio-testimonial.php';
require_once $pluginDir . '/includes/class-portfolio-project.php';
$testimonials = \PortfolioTestimonial::getAll();
$projects = \PortfolioProject::getAll(['status' => 'published'], 1, 100);
ob_start();
?>
<style>
.pf-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.pf-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.pf-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-pf{background:linear-gradient(135deg,#7c3aed 0%,#a855f7 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.pf-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.pf-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.testimonial-item{padding:14px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.testimonial-item:last-child{border-bottom:none}
.stars{color:#f59e0b;font-size:.85rem}
.status-badge{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;text-transform:uppercase}
.status-published{background:rgba(16,185,129,.15);color:#34d399}
.status-pending{background:rgba(245,158,11,.15);color:#fbbf24}
.form-add{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px}
.form-add .full{grid-column:1/-1}
.form-add label{display:block;font-size:.72rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.form-add input,.form-add select,.form-add textarea{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.82rem;box-sizing:border-box;font-family:inherit}
.form-add textarea{min-height:60px;resize:vertical}
@media(max-width:600px){.form-add{grid-template-columns:1fr}}
</style>
<div class="pf-wrap">
    <div class="pf-header"><h1>💬 Testimonials</h1><a href="/admin/portfolio" class="btn-secondary">← Dashboard</a></div>
    <div class="pf-card">
        <h3>➕ Add Testimonial</h3>
        <form method="post" action="/admin/portfolio/testimonials/store">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="form-add">
                <div><label>Client Name *</label><input type="text" name="client_name" required></div>
                <div><label>Client Title</label><input type="text" name="client_title" placeholder="CEO, CTO..."></div>
                <div><label>Company</label><input type="text" name="client_company"></div>
                <div><label>Project</label><select name="project_id"><option value="">— General —</option><?php foreach ($projects['projects'] as $p): ?><option value="<?= $p['id'] ?>"><?= h($p['title']) ?></option><?php endforeach; ?></select></div>
                <div class="full"><label>Testimonial *</label><textarea name="content" required placeholder="What the client said..."></textarea></div>
                <div><label>Rating</label><select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= str_repeat('★', $i) ?></option><?php endfor; ?></select></div>
                <div><label>Status</label><select name="status"><option value="published">Published</option><option value="pending">Pending</option></select></div>
                <div><label>Featured</label><select name="is_featured"><option value="0">No</option><option value="1">Yes ⭐</option></select></div>
                <div><label>Photo URL</label><input type="text" name="client_photo" placeholder="https://..."></div>
            </div>
            <button type="submit" class="btn-pf">➕ Add Testimonial</button>
        </form>
    </div>
    <div class="pf-card">
        <h3>📋 All Testimonials (<?= count($testimonials) ?>)</h3>
        <?php foreach ($testimonials as $t): ?>
        <div class="testimonial-item">
            <div style="display:flex;align-items:flex-start;gap:12px">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <strong style="font-size:.9rem;color:var(--text)"><?= h($t['client_name']) ?></strong>
                        <?php if ($t['client_title'] || $t['client_company']): ?>
                        <span style="font-size:.75rem;color:var(--muted)"><?= h(implode(', ', array_filter([$t['client_title'], $t['client_company']]))) ?></span>
                        <?php endif; ?>
                        <span class="stars"><?= str_repeat('★', (int)$t['rating']) ?></span>
                        <span class="status-badge status-<?= h($t['status']) ?>"><?= h($t['status']) ?></span>
                        <?php if ($t['is_featured']): ?><span style="font-size:.65rem;color:#f59e0b">⭐ FEATURED</span><?php endif; ?>
                    </div>
                    <?php if ($t['project_title']): ?><div style="font-size:.75rem;color:var(--muted);margin-top:2px">Project: <?= h($t['project_title']) ?></div><?php endif; ?>
                    <div style="font-size:.82rem;color:var(--text);margin-top:6px;opacity:.85">"<?= h($t['content']) ?>"</div>
                    <div style="font-size:.7rem;color:var(--muted);margin-top:4px"><?= date('M j, Y', strtotime($t['created_at'])) ?></div>
                </div>
                <div style="display:flex;gap:4px;flex-shrink:0">
                    <?php if ($t['status'] === 'pending'): ?>
                    <form method="post" action="/admin/portfolio/testimonials/<?= $t['id'] ?>/approve" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" style="background:rgba(16,185,129,.15);color:#34d399;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">✓</button></form>
                    <?php endif; ?>
                    <form method="post" action="/admin/portfolio/testimonials/<?= $t['id'] ?>/delete" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">🗑</button></form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($testimonials)): ?><p style="color:var(--muted);font-size:.85rem">No testimonials yet.</p><?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Portfolio Testimonials'; require CMS_APP . '/views/admin/layouts/topbar.php';
