<?php
/**
 * Admin Login - Modern Dark UI
 */
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || !DEV_MODE) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/auth.php';
csrf_boot('admin');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    [$ok, $user] = authenticateAdmin($u, $p, 'admins');
    if ($ok) {
        $csrfToken = $_SESSION['csrf_token'] ?? null;
        session_regenerate_id(true);
        if ($csrfToken) $_SESSION['csrf_token'] = $csrfToken;
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_username'] = $user['username'] ?? $u;
        $_SESSION['admin_id'] = $user['id'] ?? $user['admin_id'] ?? null;
        $_SESSION['admin_user_id'] = $user['id'] ?? null;
        $_SESSION['admin_role'] = 'admin';
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['init_ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['last_regeneration'] = time();
        $_SESSION['admin_initiated'] = true;
        session_write_close();
        header('Location: /admin/', true, 303);
        exit;
    }
    $error = 'Invalid credentials';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Jessie AI-CMS</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--danger:#f38ba8;--purple:#cba6f7;--border:#313244}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh;display:flex;align-items:center;justify-content:center}
.login-box{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:40px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.logo{text-align:center;margin-bottom:32px}
.logo-icon{font-size:48px;margin-bottom:12px}
.logo-icon img{width:64px;height:64px;border-radius:16px;background:var(--bg3);padding:8px}
.logo h1{font-size:22px;font-weight:600}
.logo p{font-size:13px;color:var(--muted)}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:12px;font-weight:500;margin-bottom:8px;color:var(--text2)}
.form-group input{width:100%;padding:14px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;transition:.15s}
.form-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.btn{width:100%;padding:14px;font-size:14px;font-weight:600;background:var(--accent);color:#000;border:none;border-radius:10px;cursor:pointer;transition:.15s}
.btn:hover{background:var(--purple)}
.error{background:rgba(243,139,168,.15);border:1px solid rgba(243,139,168,.3);color:var(--danger);padding:12px 16px;border-radius:10px;margin-bottom:20px;text-align:center;font-size:13px}
.footer{text-align:center;margin-top:24px;font-size:12px;color:var(--muted)}
</style>
</head>
<body>
<div class="login-box">
<div class="logo">
<div class="logo-icon"><img src="/assets/images/jessie-logo.svg" alt="Jessie" width="64" height="64"></div>
<h1>Jessie AI-CMS</h1>
<p>Sign in to access the admin panel</p>
</div>

<?php if ($error): ?>
<div class="error">❌ <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="post">
<?php csrf_field(); ?>
<div class="form-group">
<label>Username</label>
<input type="text" name="username" placeholder="Enter your username" required autofocus>
</div>
<div class="form-group">
<label>Password</label>
<input type="password" name="password" placeholder="Enter your password" required>
</div>
<button type="submit" class="btn">Sign In →</button>
</form>

<div class="footer">Jessie AI-CMS</div>
</div>
</body>
</html>
