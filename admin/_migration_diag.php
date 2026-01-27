<?php
// Admin diagnostics for migration manager (READ-ONLY)
// NOT LARAVEL. No Schema::, up(), down(), Artisan, Composer, Illuminate, or CLI assumptions.

$ADMIN_DIR = __DIR__;
$ROOT_DIR  = dirname(__DIR__);

// Require admin auth (fail safe with clear message if missing)
$authPath = $ADMIN_DIR . '/includes/auth.php';
if (!file_exists($authPath)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "ERROR: admin/includes/auth.php not found at: {$authPath}\n";
    return;
}
require_once $authPath;

header('Content-Type: text/html; charset=utf-8');

// Paths to check
$checks = [
    'admin/includes/auth.php' => $authPath,
    'migrate.php' => $ROOT_DIR . '/migrate.php',
    'includes/migrations/migration_registry.php' => $ROOT_DIR . '/includes/migrations/migration_registry.php',
    'includes/migrations/migrations_log.json' => $ROOT_DIR . '/includes/migrations/migrations_log.json',
];

function fexists($p){ return file_exists($p) ? 'yes' : 'no'; }
function freadable($p){ return is_readable($p) ? 'yes' : 'no'; }

// Try DRY-RUN require_once capture (no execution, no log writes)
$dryRunOutput = '';
$runner = $ROOT_DIR . '/migrate.php';
if (file_exists($runner)) {
    ob_start();
    $mode = 'dry-run';
    require_once $runner;
    $dryRunOutput = ob_get_clean();
}
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Migration Diagnostics (READ-ONLY)</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;margin:24px}
h1{margin:0 0 12px}
section{margin:18px 0}
pre{white-space:pre-wrap;word-wrap:break-word;padding:12px;border:1px solid #ddd;border-radius:8px}
table{border-collapse:collapse;width:100%;max-width:900px}
td,th{border:1px solid #ddd;padding:6px 8px;text-align:left}
.bad{color:#a40000;font-weight:600}
.good{color:#006400;font-weight:600}
.small{font-size:.9em;color:#666}
</style>
</head>
<body>
<h1>Migration Diagnostics (READ-ONLY)</h1>

<section>
  <h2>Environment</h2>
  <table>
    <tr><th>PHP</th><td><?php echo htmlspecialchars(PHP_VERSION); ?></td></tr>
    <tr><th>__DIR__</th><td><?php echo htmlspecialchars(__DIR__); ?></td></tr>
    <tr><th>$ROOT_DIR</th><td><?php echo htmlspecialchars($ROOT_DIR); ?></td></tr>
  </table>
  <p class="small">If your web server's DocumentRoot is not the CMS root (<?php echo htmlspecialchars($ROOT_DIR); ?>), adjust the URL accordingly (e.g. add <code>/cms</code> prefix).</p>
</section>

<section>
  <h2>Path Checks</h2>
  <table>
    <tr><th>File</th><th>Resolved path</th><th>exists</th><th>readable</th><th>realpath</th></tr>
    <?php foreach ($checks as $label => $path): ?>
      <tr>
        <td><?php echo htmlspecialchars($label); ?></td>
        <td><?php echo htmlspecialchars($path); ?></td>
        <td class="<?php echo file_exists($path)?'good':'bad'; ?>"><?php echo fexists($path); ?></td>
        <td class="<?php echo is_readable($path)?'good':'bad'; ?>"><?php echo freadable($path); ?></td>
        <td><?php echo htmlspecialchars(@realpath($path) ?: ''); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</section>

<section>
  <h2>DRY-RUN Preview (from migrate.php)</h2>
  <pre><?php echo htmlspecialchars($dryRunOutput ?: 'No output captured (check migrate.php).'); ?></pre>
  <p class="small">This preview does not execute migrations and does not modify logs.</p>
</section>

<section>
  <h2>Quick Links</h2>
  <ul>
    <li><a href="migration_manager.php">Open Migration Manager</a></li>
    <li><a href="_migration_diag.php">Refresh Diagnostics</a></li>
    <li><a href="../dryrun_preview.php" target="_blank">Standalone DRY-RUN Preview</a></li>
  </ul>
</section>
</body>
</html>
