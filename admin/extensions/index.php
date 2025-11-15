<?php
declare(strict_types=1);
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/../includes/flashmessage.php';
cms_session_start('admin');
csrf_boot('admin');

if (!function_exists('fm_all')) {
    function fm_all(): array {
        $msgs = [];
        if (class_exists('FlashMessage')) {
            if (method_exists('FlashMessage', 'getAll')) {
                $msgs = FlashMessage::getAll();
            } elseif (method_exists('FlashMessage', 'getMessages')) {
                $msgs = FlashMessage::getMessages();
            } elseif (method_exists('FlashMessage', 'pullAll')) {
                $msgs = FlashMessage::pullAll();
            }
        }
        if (empty($msgs) && isset($_SESSION['flash_messages'])) {
            $msgs = $_SESSION['flash_messages'];
            unset($_SESSION['flash_messages']);
        }
        return is_array($msgs) ? $msgs : [];
    }
}

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once __DIR__ . '/../../core/extensions_state.php';
$EXT_DIR = CMS_ROOT . '/extensions';
$messages = fm_all();

// Handle filter parameter
$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'enabled', 'disabled'], true)) {
    $filter = 'all';
}

function list_extensions(string $base, string $filter = 'all'): array {
    $out = [];
    if (!is_dir($base)) return $out;
    $states = ext_state_load();
    foreach (glob($base . '/*/extension.json') ?: [] as $mf) {
        $dir = dirname($mf);
        $slug = basename($dir);
        $meta = @json_decode((string)@file_get_contents($mf), true) ?: [];
        $status = $states[$slug] ?? 'enabled';
        
        // Apply filter
        if ($filter === 'enabled' && $status !== 'enabled') continue;
        if ($filter === 'disabled' && $status !== 'disabled') continue;
        
        $out[] = [
            'slug' => $slug,
            'name' => (string)($meta['name'] ?? $slug),
            'version' => (string)($meta['version'] ?? ''),
            'path' => $dir,
            'status' => $status,
        ];
    }
    return $out;
}
$exts = list_extensions($EXT_DIR, $filter);

// Check for disabled extensions for banner
$all_exts = list_extensions($EXT_DIR, 'all');
$disabled_count = count(array_filter($all_exts, function($ext) { return $ext['status'] === 'disabled'; }));
?><!doctype html><meta charset="utf-8"><title>Extensions</title>
<style>body{font-family:system-ui;margin:20px} table{border-collapse:collapse;width:100%} th,td{border:1px solid #ccc;padding:8px} .flash{padding:8px;margin:8px 0;border-radius:6px}.ok{background:#d4edda}.err{background:#f8d7da} .status-enabled{background:#d4edda;color:#155724;padding:2px 6px;border-radius:4px} .status-disabled{background:#f8d7da;color:#721c24;padding:2px 6px;border-radius:4px} .btn{padding:4px 8px;margin:2px;border:1px solid #ccc;background:#fff;cursor:pointer} .btn-enable{background:#d4edda} .btn-disable{background:#f8d7da} .filter-bar{margin:15px 0;padding:10px;background:#f8f9fa;border-radius:6px} .filter-bar a{padding:6px 12px;margin:0 5px;text-decoration:none;border-radius:4px;background:#fff;border:1px solid #ccc} .filter-bar a.active{background:#007bff;color:#fff}</style>
<h1>Installed Extensions</h1>
<p><a href="upload.php">Upload new extension</a></p>
<?php foreach ($messages as $m): ?>
  <div class="flash <?= $m['type']==='success'?'ok':'err' ?>"><?= htmlspecialchars($m['text'],ENT_QUOTES,'UTF-8') ?></div>
<?php endforeach; ?><?php if ($disabled_count >= 1): ?>
  <div class="flash err">Some extensions are disabled. <a href="?filter=disabled">View disabled</a></div>
<?php endif; ?>
<div class="filter-bar">
  <strong>Filter:</strong>
  <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">All</a>
  <a href="?filter=enabled" class="<?= $filter === 'enabled' ? 'active' : '' ?>">Enabled</a>
  <a href="?filter=disabled" class="<?= $filter === 'disabled' ? 'active' : '' ?>">Disabled</a>
</div>

<?php if (!$exts): ?>
  <p>No extensions found<?= $filter !== 'all' ? ' with current filter' : '' ?>.</p>
<?php else: ?>
<table>
  <tr><th>Slug</th><th>Name</th><th>Version</th><th>Status</th><th>Actions</th></tr>
  <?php foreach ($exts as $e): ?>
  <tr>
    <td><?= htmlspecialchars($e['slug']) ?></td>
    <td><?= htmlspecialchars($e['name']) ?></td>
    <td><?= htmlspecialchars($e['version']) ?></td>
    <td><span class="status-<?= $e['status'] ?>"><?= ucfirst($e['status']) ?></span></td>
    <td>
      <?php if ($e['status'] === 'enabled'): ?>
        <form method="post" action="toggle.php" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>">
          <input type="hidden" name="slug" value="<?= htmlspecialchars($e['slug'],ENT_QUOTES,'UTF-8') ?>">
          <input type="hidden" name="action" value="disable">
          <button type="submit" class="btn btn-disable">Disable</button>
        </form>
      <?php else: ?>
        <form method="post" action="toggle.php" style="display:inline">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>">
          <input type="hidden" name="slug" value="<?= htmlspecialchars($e['slug'],ENT_QUOTES,'UTF-8') ?>">
          <input type="hidden" name="action" value="enable">
          <button type="submit" class="btn btn-enable">Enable</button>
        </form>
      <?php endif; ?>
      <form method="post" action="verify.php" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="slug" value="<?= htmlspecialchars($e['slug'],ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="action" value="build">
        <button type="submit" class="btn">Build Baseline</button>
      </form>
      <form method="post" action="verify.php" style="display:inline">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="slug" value="<?= htmlspecialchars($e['slug'],ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="action" value="check">
        <button type="submit" class="btn">Verify Now</button>
      </form>
      <form method="post" action="uninstall.php" style="display:inline" onsubmit="return confirm('Uninstall <?= htmlspecialchars($e['slug']) ?>?');">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>">
        <input type="hidden" name="slug" value="<?= htmlspecialchars($e['slug'],ENT_QUOTES,'UTF-8') ?>">
        <button type="submit" class="btn">Uninstall</button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif;
