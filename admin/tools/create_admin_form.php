<?php
declare(strict_types=1);

define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot();
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Admin (DEV)</title>
  <style>
    body{max-width:480px;margin:3rem auto;font-family:system-ui,Segoe UI,Roboto,Arial,sans-serif;line-height:1.4}
    label{display:block;margin:.5rem 0 .25rem}
    input[type=text],input[type=password]{width:100%;padding:.5rem;border:1px solid #ccc;border-radius:6px}
    button{margin-top:1rem;padding:.6rem 1rem;border:0;border-radius:6px;cursor:pointer}
  </style>
  </head>
<body>
  <h1>Create Admin (DEV)</h1>
  <form method="post" action="/admin/tools/create_admin.php">
    <?php echo csrf_field(); ?>
    <label for="u">Username</label>
    <input id="u" name="u" type="text" required>
    <label for="p">Password</label>
    <input id="p" name="p" type="password" required>
    <button type="submit">Create admin</button>
  </form>
  <p><a href="/admin/login.php">Back to login</a></p>
</body>
</html>
