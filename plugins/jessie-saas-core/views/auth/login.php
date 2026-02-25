<?php
$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Jessie AI</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f172a;--bg2:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#64748b;--accent:#8b5cf6;--accent2:#a78bfa;--danger:#ef4444}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center}
.auth-box{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:48px;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,.3)}
.auth-logo{text-align:center;margin-bottom:32px}
.auth-logo h1{font-size:28px;font-weight:700;background:linear-gradient(135deg,var(--accent),#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.auth-logo p{color:var(--muted);font-size:14px;margin-top:6px}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:500;color:#94a3b8;margin-bottom:8px}
.form-group input{width:100%;padding:14px 16px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px}
.form-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(139,92,246,.15)}
.btn{width:100%;padding:14px;font-size:15px;font-weight:600;background:var(--accent);color:#fff;border:none;border-radius:10px;cursor:pointer;transition:.15s}
.btn:hover{background:var(--accent2)}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:var(--danger);padding:12px;border-radius:10px;margin-bottom:20px;text-align:center;font-size:13px}
.auth-footer{text-align:center;margin-top:24px;color:var(--muted);font-size:13px}
.auth-footer a{color:var(--accent);text-decoration:none}
</style>
</head><body>
<div class="auth-box">
    <div class="auth-logo">
        <h1>🐕 Jessie AI</h1>
        <p>Sign in to your AI workspace</p>
    </div>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="/saas/login">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="you@company.com" required autofocus></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="••••••••" required></div>
        <button type="submit" class="btn">Sign In →</button>
    </form>
    <div class="auth-footer">Don't have an account? <a href="/saas/register">Create one free</a><br><a href="/saas/forgot-password" style="color:var(--muted)">Forgot password?</a></div>
</div>
</body></html>
