<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-directory-category.php';
$categories = \DirectoryCategory::getAll();
ob_start();
?>
<style>
.dir-wrap{max-width:800px;margin:0 auto;padding:24px 20px}
.dir-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.dir-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-dir{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:none;cursor:pointer;text-decoration:none}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.dir-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:20px;margin-bottom:20px}
.dir-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:var(--muted,#94a3b8);margin:0 0 14px;padding-bottom:8px;border-bottom:1px solid var(--border,#334155)}
.cat-row{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid rgba(51,65,85,.5)}
.cat-row:last-child{border-bottom:none}
.cat-icon{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:1.1rem}
.form-inline{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px}
.form-inline .fg{flex:1;min-width:120px}.form-inline .fg label{display:block;font-size:.72rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:4px}
.form-inline .fg input{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:8px 10px;border-radius:6px;font-size:.82rem;box-sizing:border-box}
</style>
<div class="dir-wrap">
    <div class="dir-header"><h1>📁 Categories</h1><a href="/admin/directory" class="btn-secondary">← Dashboard</a></div>
    <div class="dir-card">
        <h3>➕ Add Category</h3>
        <form method="post" action="/admin/directory/categories/store" class="form-inline">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <div class="fg"><label>Name *</label><input type="text" name="name" required></div>
            <div class="fg" style="max-width:80px"><label>Icon</label><input type="text" name="icon" placeholder="🏢"></div>
            <div class="fg" style="max-width:80px"><label>Color</label><input type="color" name="color" value="#6366f1" style="height:36px;padding:2px"></div>
            <button type="submit" class="btn-dir" style="height:36px;padding:8px 16px">➕</button>
        </form>
    </div>
    <div class="dir-card">
        <h3>📋 All Categories</h3>
        <?php foreach ($categories as $c): ?>
        <div class="cat-row">
            <div class="cat-icon" style="background:<?= h($c['color']) ?>20;color:<?= h($c['color']) ?>"><?= h($c['icon'] ?: '📁') ?></div>
            <div style="flex:1"><strong style="font-size:.85rem;color:var(--text)"><?= h($c['name']) ?></strong><br><span style="font-size:.72rem;color:var(--muted)"><?= (int)$c['listing_count'] ?> listings</span></div>
            <form method="post" action="/admin/directory/categories/<?= $c['id'] ?>/delete" style="margin:0"><input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>"><button type="submit" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.1);color:#fca5a5;border:none;padding:4px 10px;border-radius:4px;cursor:pointer;font-size:.75rem">🗑</button></form>
        </div>
        <?php endforeach; ?>
        <?php if (empty($categories)): ?><p style="color:var(--muted);font-size:.85rem">No categories yet.</p><?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Directory Categories'; require CMS_APP . '/views/admin/layouts/topbar.php';
