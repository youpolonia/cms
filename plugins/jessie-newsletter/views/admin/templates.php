<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pdo = db();
$templates = $pdo->query("SELECT * FROM newsletter_templates ORDER BY is_default DESC, created_at DESC")->fetchAll(\PDO::FETCH_ASSOC);
ob_start();
?>
<style>
.nl-wrap{max-width:1000px;margin:0 auto;padding:24px 20px}
.nl-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.nl-header h1{font-size:1.5rem;font-weight:700;color:var(--text,#e2e8f0);margin:0}
.btn-secondary{background:var(--bg-card,#1e293b);color:var(--text,#e2e8f0);padding:10px 20px;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--border,#334155);text-decoration:none}
.tpl-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
.tpl-card{background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:12px;overflow:hidden;transition:.2s}
.tpl-card:hover{border-color:#6366f1}
.tpl-preview{background:#fff;height:160px;overflow:hidden;padding:16px;font-size:10px;color:#333;line-height:1.4}
.tpl-info{padding:14px}
.tpl-info h4{margin:0 0 4px;font-size:.9rem;color:var(--text,#e2e8f0)}
.tpl-info .meta{font-size:.72rem;color:var(--muted,#94a3b8)}
.tpl-info .actions{margin-top:8px;display:flex;gap:8px}
.tpl-info .actions a{font-size:.78rem;color:#a5b4fc;text-decoration:none}
</style>
<div class="nl-wrap">
    <div class="nl-header"><h1>🎨 Templates</h1><a href="/admin/newsletter" class="btn-secondary">← Dashboard</a></div>
    <div class="tpl-grid">
        <?php foreach ($templates as $t): ?>
        <div class="tpl-card">
            <div class="tpl-preview"><?= $t['content_html'] ?></div>
            <div class="tpl-info">
                <h4><?= h($t['name']) ?> <?= $t['is_default'] ? '<span style="font-size:.65rem;color:var(--muted)">DEFAULT</span>' : '' ?></h4>
                <div class="meta"><?= h(ucfirst($t['category'])) ?></div>
                <div class="actions"><a href="/admin/newsletter/campaigns/create?template_id=<?= $t['id'] ?>">✉️ Use in Campaign</a></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php $content = ob_get_clean(); $title = 'Email Templates'; require CMS_APP . '/views/admin/layouts/topbar.php';
