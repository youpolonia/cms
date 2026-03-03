<?php
$pageTitle = $title ?? 'Not Found';
ob_start();
?>
<div style="text-align:center;padding:60px 20px;">
    <h1 style="font-size:4rem;color:#94a3b8;margin:0;">404</h1>
    <h2 style="color:#334155;margin:12px 0;"><?= h($pageTitle) ?></h2>
    <p style="color:#64748b;"><?= h($message ?? 'The requested item was not found.') ?></p>
    <a href="/admin" style="display:inline-block;margin-top:20px;padding:10px 24px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;">
        ← Back to Dashboard
    </a>
</div>
<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
?>
