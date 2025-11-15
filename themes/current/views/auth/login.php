<?php
// Auth Login (theme view bridge). Prefer core login view if present.
$__core = __DIR__ . '/../../../../includes/views/auth/login.php';
if (is_file($__core)) {
    $base = realpath(__DIR__ . '/../../../../includes/views/auth');
    $target = realpath($__core);
    if ($base === false || $target === false || substr_compare($target, $base . DIRECTORY_SEPARATOR, 0, strlen($base) + 1) !== 0 || !is_file($target)) {
        error_log("SECURITY: blocked dynamic include: auth/login.php");
        return;
    }
    require_once $target;
    return;
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Logowanie</title></head>
<body>
<h1>Logowanie</h1>
<p>Widok logowania tymczasowo nie został dostarczony — skontaktuj się z administratorem.</p>
</body>
</html>
