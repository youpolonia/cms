<?php
declare(strict_types=1);

require_once __DIR__ . '/../config.php';        // single source of truth
require_once __DIR__ . '/../core/csrf.php';
csrf_boot();
if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }

require_once __DIR__ . '/../core/session_boot.php';

cms_session_start('admin');                      // ensure session is open

// Strong logout: clear data, destroy, expire cookie, regen ID, then 303 redirect
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], !empty($p['secure']), !empty($p['httponly']));
}

session_destroy();
session_regenerate_id(true);

header('Location: /admin/login.php', true, 303);
exit;
