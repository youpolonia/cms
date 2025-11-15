<?php
// Multisite Index (theme view stub). If a core view exists, render it directly.
$__core = __DIR__ . '/../../../../includes/views/admin/multisite/index.php';
if (is_file($__core)) {
    require_once $__core;
    return;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Multisite — Index</title></head>
<body>
<h1>Multisite — Lista serwisów</h1>
<?php if (isset($sites) && is_array($sites)): ?>
    <ul>
        <?php foreach ($sites as $sid => $cfg): ?>
            <li><strong><?php echo htmlspecialchars((string)$sid, ENT_QUOTES); ?></strong></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <p>Brak danych multisite.</p>
<?php endif; ?>
</body>
</html>
