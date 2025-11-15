<?php
// Multisite Edit (theme view stub). Prefer core view if present.
$__core = __DIR__ . '/../../../../includes/views/admin/multisite/edit.php';
if (is_file($__core)) {
    require_once $__core;
    return;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Multisite — Edit</title></head>
<body>
<h1>Edytuj serwis</h1>
<?php if (isset($siteId)): ?>
    <p>Edytujesz: <strong><?php echo htmlspecialchars((string)$siteId, ENT_QUOTES); ?></strong></p>
<?php endif; ?>
<p>Brak bazowego widoku — to jest minimalny placeholder motywu.</p>
</body>
</html>
