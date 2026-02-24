<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pluginDir = dirname(__DIR__, 2);
require_once $pluginDir . '/includes/class-newsletter-list.php';
$lists = \NewsletterList::getAll('active');
ob_start();
?>
<style>
.nl-wrap{max-width:600px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.nl-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;padding:24px;margin-bottom:20px}
.form-group{margin-bottom:16px}.form-group label{display:block;font-size:.8rem;font-weight:600;color:var(--text,#e2e8f0);margin-bottom:6px}
.form-group input,.form-group select{width:100%;background:var(--bg,#0f172a);border:1px solid var(--border,#334155);color:var(--text,#e2e8f0);padding:10px 12px;border-radius:8px;font-size:.85rem;box-sizing:border-box}
.btn-nl{background:linear-gradient(135deg,#6366f1 0%,#8b5cf6 100%);color:#fff;padding:10px 24px;border-radius:8px;font-size:.9rem;font-weight:600;border:none;cursor:pointer}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.check-row{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.check-row input{width:18px;height:18px;accent-color:#6366f1}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>➕ Add Subscriber</h1><a href="/admin/newsletter/subscribers" class="btn-secondary">← Back</a></div>
    <form method="post" action="/admin/newsletter/subscribers/store">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <div class="nl-card">
            <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Name</label><input type="text" name="name"></div>
            <div class="form-group"><label>Add to Lists</label>
                <?php foreach ($lists as $l): ?>
                <div class="check-row"><input type="checkbox" name="lists[]" value="<?= $l['id'] ?>" id="lst-<?= $l['id'] ?>"><label for="lst-<?= $l['id'] ?>"><?= h($l['name']) ?></label></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="display:flex;gap:12px;justify-content:flex-end"><a href="/admin/newsletter/subscribers" class="btn-secondary">Cancel</a><button type="submit" class="btn-nl">➕ Add Subscriber</button></div>
    </form>
</div>
<?php $content = ob_get_clean(); $title = 'Add Subscriber'; require CMS_APP . '/views/admin/layouts/topbar.php';
