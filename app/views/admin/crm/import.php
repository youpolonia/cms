<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Import Contacts';
ob_start();
?>
<div style="max-width:600px;margin:0 auto">
    <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:20px">📥 Import Contacts</h1>
    <div style="background:var(--bg-card,#1e293b);border:1px solid var(--border,#334155);border-radius:10px;padding:24px">
        <h3 style="font-size:1rem;color:var(--text,#e2e8f0);margin-bottom:12px">From Contact Form Submissions</h3>
        <p style="font-size:.85rem;color:var(--muted,#94a3b8);margin-bottom:16px">
            Import contacts from your website's contact form. Only new contacts (not already in CRM) will be imported.
        </p>
        <p style="font-size:1.2rem;font-weight:700;color:var(--text,#e2e8f0);margin-bottom:16px">
            <?= $importable ?> contact<?= $importable === 1 ? '' : 's' ?> ready to import
        </p>
        <?php if ($importable > 0): ?>
        <form method="post" action="/admin/crm/import">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <button type="submit" style="padding:12px 24px;border-radius:8px;background:var(--primary,#6366f1);color:#fff;border:none;cursor:pointer;font-size:.95rem;font-weight:600">Import <?= $importable ?> Contact<?= $importable === 1 ? '' : 's' ?></button>
        </form>
        <?php else: ?>
        <p style="color:var(--muted);font-size:.85rem">All form submissions are already imported! ✅</p>
        <?php endif; ?>
    </div>
    <div style="text-align:center;margin-top:16px"><a href="/admin/crm" style="color:var(--primary,#6366f1);font-size:.85rem">← Back to CRM</a></div>
</div>
<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
