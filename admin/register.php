<?php
/**
 * Admin Register Page (pure PHP, safe + CSRF)
 */
declare(strict_types=1);
define('CMS_ENTRY_POINT', true);

require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once CMS_ROOT . '/core/error_handler.php';
cms_register_error_handlers();

require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');

// optional version header in admin
if (is_file(CMS_ROOT . '/version.php')) { require_once CMS_ROOT . '/version.php'; }

// soft-load auth classes if present
$authPath = CMS_ROOT . '/modules/auth/authcontroller.php';
$userPath = CMS_ROOT . '/modules/auth/User.php';
if (is_file($authPath)) { require_once $authPath; }
if (is_file($userPath)) { require_once $userPath; }

if (!function_exists('h')) {
    function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

$errors = [];
$notice = '';
$ac = null;

// If AuthController exists, instantiate guarded
if (class_exists('AuthController') && isset($dbConnection)) {
    try { $ac = new AuthController($dbConnection); } catch (Throwable $e) { $notice = 'Authentication subsystem unavailable.'; }
}

// Redirect if already logged in (guarded)
if ($ac && method_exists($ac, 'isLoggedIn') && $ac->isLoggedIn()) {
    header('Location: index.php'); exit;
}

// Handle POST
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    csrf_validate_or_403();
    $username   = trim((string)($_POST['username'] ?? ''));
    $password   = (string)($_POST['password'] ?? '');
    $password2  = (string)($_POST['password_confirm'] ?? '');

    if ($username === '' || $password === '' || $password2 === '') {
        $errors[] = 'All fields are required.';
    } elseif ($password !== $password2) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $registered = false;
        try {
            // 1) Preferred: AuthController::register
            if ($ac && method_exists($ac, 'register')) {
                $registered = (bool)$ac->register($username, $password);
            }
            // 2) Fallback: User::createUser(...) with signature detection
            elseif (class_exists('User') && method_exists('User', 'createUser')) {
                $ref = new ReflectionMethod('User', 'createUser');
                $argc = $ref->getNumberOfParameters();
                $hash = password_hash($password, PASSWORD_DEFAULT);
                if ($argc >= 3 && isset($dbConnection)) {
                    $registered = (bool)User::createUser($dbConnection, $username, $hash);
                } elseif ($argc >= 2) {
                    $registered = (bool)User::createUser($username, $hash);
                } else {
                    $errors[] = 'Registration backend signature mismatch.';
                }
            } else {
                $errors[] = 'Registration backend is not available.';
            }
        } catch (Throwable $e) {
            $errors[] = 'Registration failed.';
        }

        if ($registered) { header('Location: login.php'); exit; }
    }
}

http_response_code(!empty($errors) ? 422 : 200);
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, sans-serif; background:#f8fafc; margin:0; padding:40px; color:#0f172a; }
    .card { max-width:420px; margin:0 auto; background:#fff; border-radius:12px; box-shadow: 0 4px 20px rgba(0,0,0,.06); padding:24px; }
    .h { margin:0 0 16px; font-size:20px; font-weight:700; }
    .f { display:flex; flex-direction:column; gap:12px; }
    label { font-size:12px; color:#475569; }
    input { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:10px 12px; font-size:14px; }
    .btn { background:#111827; color:#fff; border:none; border-radius:8px; padding:10px 12px; cursor:pointer; font-weight:600; }
    .btn:hover { opacity:.95; }
    .err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; padding:10px 12px; border-radius:8px; margin-bottom:12px; }
    .note { color:#64748b; font-size:12px; margin-top:8px; }
  </style>
<?php if (defined('CMS_VERSION')): ?>
  <meta name="x-cms-version" content="<?= h(CMS_VERSION) ?>">
<?php endif; ?>
</head>
<body>
  <div class="card">
    <h1 class="h">Create admin account</h1>
    <?php if (!empty($errors)): ?>
      <div class="err">
        <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
      </div>
    <?php elseif ($notice): ?>
      <div class="note"><?= h($notice) ?></div>
    <?php endif; ?>
    <form method="post" class="f" autocomplete="off">
      <?php csrf_field(); 
?>      <div>
        <label for="username">Username</label>
        <input id="username" name="username" required>
      </div>
      <div>
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required>
      </div>
      <div>
        <label for="password_confirm">Confirm Password</label>
        <input id="password_confirm" name="password_confirm" type="password" required>
      </div>
      <button class="btn">Register</button>
      <div class="note">Already have an account? <a href="login.php">Sign in</a></div>
    </form>
  </div>
</body>
</html>
