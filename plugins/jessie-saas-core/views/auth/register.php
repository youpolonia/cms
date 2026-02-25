<?php $pageTitle = 'Register'; ?>
<!DOCTYPE html>
<html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — Jessie AI</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f172a;--bg2:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#64748b;--accent:#8b5cf6;--accent2:#a78bfa;--danger:#ef4444;--success:#22c55e}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center}
.auth-box{background:var(--bg2);border:1px solid var(--border);border-radius:20px;padding:48px;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,.3)}
.auth-logo{text-align:center;margin-bottom:32px}
.auth-logo h1{font-size:28px;font-weight:700;background:linear-gradient(135deg,var(--accent),#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.auth-logo p{color:var(--muted);font-size:14px;margin-top:6px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:13px;font-weight:500;color:#94a3b8;margin-bottom:6px}
.form-group input{width:100%;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px}
.form-group input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(139,92,246,.15)}
.btn{width:100%;padding:14px;font-size:15px;font-weight:600;background:var(--accent);color:#fff;border:none;border-radius:10px;cursor:pointer}
.btn:hover{background:var(--accent2)}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:var(--danger);padding:12px;border-radius:10px;margin-bottom:20px;text-align:center;font-size:13px}
.auth-footer{text-align:center;margin-top:24px;color:var(--muted);font-size:13px}
.auth-footer a{color:var(--accent);text-decoration:none}
.features{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:24px}
.feature{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px}
.feature span{color:var(--success)}
</style>
</head><body>
<div class="auth-box">
    <div class="auth-logo">
        <h1>🐕 Jessie AI</h1>
        <p>Create your free account</p>
    </div>
    <div class="features">
        <div class="feature"><span>✓</span> 10 AI tools</div>
        <div class="feature"><span>✓</span> 100 free credits</div>
        <div class="feature"><span>✓</span> API access</div>
        <div class="feature"><span>✓</span> No card required</div>
    </div>
    <?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post" action="/saas/register">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <div class="form-group"><label>Full Name</label><input type="text" name="name" placeholder="John Doe"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" placeholder="you@company.com" required></div>
        <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="Min. 8 characters" required minlength="8"></div>
        <div class="form-group"><label>Company <span style="color:var(--muted)">(optional)</span></label><input type="text" name="company" placeholder="Acme Inc"></div>
        <button type="submit" class="btn">Create Account →</button>
    </form>
    <div class="auth-footer">Already have an account? <a href="/saas/login">Sign in</a></div>
</div>
</body></html>
