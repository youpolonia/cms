<?php
/**
 * 403 Forbidden — Frontend error page
 */
http_response_code(403);
$theme = function_exists('get_active_theme') ? get_active_theme() : '';
$layoutFile = $theme ? CMS_ROOT . '/themes/' . $theme . '/layout.php' : '';
$title = 'Access Denied';
$page = ['title' => $title, 'slug' => '403', 'meta_description' => ''];

if ($layoutFile && file_exists($layoutFile)) {
    ob_start();
    ?>
    <section style="text-align:center;padding:100px 20px;min-height:50vh;display:flex;flex-direction:column;align-items:center;justify-content:center;">
        <h1 style="font-size:5rem;margin:0;opacity:0.3;">403</h1>
        <h2 style="margin:12px 0 8px;"><?= h($title) ?></h2>
        <p style="color:var(--text-secondary,#64748b);max-width:400px;">You don't have permission to access this page.</p>
        <a href="/" style="display:inline-block;margin-top:24px;padding:10px 24px;background:var(--accent,#6366f1);color:#fff;border-radius:6px;text-decoration:none;">← Go Home</a>
    </section>
    <?php
    $content = ob_get_clean();
    require $layoutFile;
} else {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>403</title></head>';
    echo '<body style="font-family:system-ui,sans-serif;text-align:center;padding:100px 20px;background:#f8fafc;">';
    echo '<h1 style="font-size:5rem;color:#cbd5e1;">403</h1><h2>Access Denied</h2>';
    echo '<p style="color:#64748b;">You don\'t have permission to access this page.</p>';
    echo '<a href="/" style="color:#6366f1;">← Go Home</a></body></html>';
}
