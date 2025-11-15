<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
$resp = null;

// Storage path
$SEO_DIR  = __DIR__ . '/../cms_storage/seo';
$SEO_FILE = $SEO_DIR . '/settings.json';

// Ensure storage directory exists
if (!is_dir($SEO_DIR)) { @mkdir($SEO_DIR, 0775, true); }

// Load current settings
$current = ['site_title'=>'','meta_desc'=>'','robots'=>'index,follow'];
if (is_file($SEO_FILE)) {
    $json = @file_get_contents($SEO_FILE);
    if ($json !== false) {
        $data = json_decode($json, true);
        if (is_array($data)) { $current = array_merge($current, $data); }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $site_title = mb_substr(trim((string)($_POST['site_title'] ?? '')), 0, 120);
    $meta_desc  = mb_substr(trim((string)($_POST['meta_desc'] ?? '')), 0, 300);
    $robots     = trim((string)($_POST['robots'] ?? 'index,follow'));

    if (!in_array($robots, ['index,follow','noindex,follow','index,nofollow','noindex,nofollow'], true)) {
        $robots = 'index,follow';
    }

    $payload = ['site_title'=>$site_title,'meta_desc'=>$meta_desc,'robots'=>$robots];
    $ok = @file_put_contents($SEO_FILE, json_encode($payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES), LOCK_EX);
    if ($ok === false) {
        $resp = ['type'=>'error','msg'=>'Failed to save SEO settings'];
    } else {
        $resp = ['type'=>'success','msg'=>'SEO settings saved','data'=>$payload];
        $current = $payload;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><title>SEO Settings</title><meta name="viewport" content="width=device-width, initial-scale=1"></head>
<body>
<main class="container">
    <h1>SEO Settings</h1>
    <?php if ($resp): ?>
        <div class="notice <?=htmlspecialchars($resp['type'])?>"><strong><?=htmlspecialchars($resp['msg'])?></strong></div>
        <?php if (!empty($resp['data'])): ?>
            <pre><?=htmlspecialchars(json_encode($resp['data'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES))?></pre>
        <?php endif; ?>
    <?php endif; ?>
    <form method="post">
        <?php csrf_field(); ?>
        <div><label for="site_title">Site Title</label><input id="site_title" name="site_title" type="text" value="<?=htmlspecialchars($current['site_title'])?>"></div>
        <div><label for="meta_desc">Default Meta Description</label><textarea id="meta_desc" name="meta_desc" rows="4"><?=htmlspecialchars($current['meta_desc'])?></textarea></div>
        <div><label for="robots">Robots</label>
            <select id="robots" name="robots">
                <?php
                $opts = ['index,follow','noindex,follow','index,nofollow','noindex,nofollow'];
                foreach ($opts as $opt) {
                    $sel = ($current['robots']===$opt)?' selected':'';
                    echo '<option value="'.htmlspecialchars($opt).'"'.$sel.'>'.htmlspecialchars($opt).'</option>';
                }
                ?>
            </select>
        </div>
        <button type="submit">Save</button>
    </form>
<?php require_once __DIR__ . '/includes/footer.php';
