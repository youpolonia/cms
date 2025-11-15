<?php
/**
 * DRY-RUN preview page for migrations.
 * NOT LARAVEL. Do not use Schema::, up(), down(), Artisan, Composer, Illuminate, or CLI.
 * Safe to open in browser; performs DRY-RUN only by including migrate.php.
 */

// Capture migrate.php output without executing migrations or writing logs.
ob_start();
require_once __DIR__ . '/migrate.php';
$output = ob_get_clean();

// Minimal HTML shell to display plain-text DRY-RUN result.
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="robots" content="noindex,nofollow">
<title>Migrations DRY-RUN Preview</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
  body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 24px; }
  pre { white-space: pre-wrap; word-wrap: break-word; padding: 12px; border: 1px solid #ddd; border-radius: 8px; }
</style>
</head>
<body>
<h1>DRY-RUN: Migrations Preview</h1>
<pre><?php echo htmlspecialchars($output ?? ''); ?></pre>
<p>This is a preview only. No migrations were executed and no logs were written.</p>
</body>
</html>
