<?php
/**
 * SaaS App Layout Shell — shared by all SaaS services
 * Usage: $pageTitle, $activeService, $content (captured via ob)
 */
if (!defined('CMS_ROOT')) exit;
$saasUser = $_SESSION['saas_email'] ?? 'Guest';
$saasName = $_SESSION['saas_name'] ?? $saasUser;
$saasPlan = $_SESSION['saas_plan'] ?? 'free';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> — Jessie AI</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f172a;--bg2:#1e293b;--bg3:#334155;--text:#e2e8f0;--text2:#94a3b8;--muted:#64748b;--accent:#8b5cf6;--accent2:#a78bfa;--success:#22c55e;--warning:#f59e0b;--danger:#ef4444;--border:#334155}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6}
.saas-layout{display:flex;min-height:100vh}
.saas-sidebar{width:240px;background:var(--bg2);border-right:1px solid var(--border);padding:20px 0;display:flex;flex-direction:column;position:fixed;height:100vh;overflow-y:auto}
.saas-logo{padding:0 20px 20px;border-bottom:1px solid var(--border);margin-bottom:12px}
.saas-logo h2{font-size:18px;font-weight:700;background:linear-gradient(135deg,var(--accent),#ec4899);-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.saas-logo p{font-size:11px;color:var(--muted);margin-top:2px}
.saas-nav{flex:1;padding:0 8px}
.saas-nav a{display:flex;align-items:center;gap:10px;padding:10px 12px;color:var(--text2);text-decoration:none;border-radius:8px;font-size:13px;transition:.15s;margin-bottom:2px}
.saas-nav a:hover,.saas-nav a.active{background:var(--bg3);color:var(--text)}
.saas-nav a.active{background:var(--accent)20;color:var(--accent)}
.saas-nav .nav-section{font-size:11px;text-transform:uppercase;color:var(--muted);padding:16px 12px 6px;font-weight:600;letter-spacing:.5px}
.saas-user{padding:12px 20px;border-top:1px solid var(--border);margin-top:auto}
.saas-user-info{font-size:13px;color:var(--text)}
.saas-user-plan{font-size:11px;color:var(--accent);text-transform:uppercase}
.saas-main{margin-left:240px;flex:1;min-height:100vh}
.saas-header{padding:16px 32px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:var(--bg2)}
.saas-header h1{font-size:20px;font-weight:700}
.saas-content{padding:32px}
.saas-card{background:var(--bg2);border:1px solid var(--border);border-radius:12px;padding:24px;margin-bottom:20px}
.saas-btn{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:.15s;text-decoration:none}
.saas-btn-primary{background:var(--accent);color:#fff}.saas-btn-primary:hover{background:var(--accent2)}
.saas-btn-ghost{background:transparent;color:var(--text2);border:1px solid var(--border)}.saas-btn-ghost:hover{background:var(--bg3)}
.saas-input{width:100%;padding:10px 14px;background:var(--bg);border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:14px}
.saas-input:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(139,92,246,.15)}
.saas-label{display:block;font-size:12px;font-weight:500;color:var(--text2);margin-bottom:6px}
</style>
</head>
<body>
<div class="saas-layout">
    <aside class="saas-sidebar">
        <div class="saas-logo">
            <h2>🐕 Jessie AI</h2>
            <p>AI-Powered Tools</p>
        </div>
        <nav class="saas-nav">
            <div class="nav-section">Main</div>
            <a href="/saas/dashboard" class="<?= ($activeService ?? '')==='dashboard'?'active':'' ?>">📊 Dashboard</a>
            <a href="/saas/profile" class="<?= ($activeService ?? '')==='profile'?'active':'' ?>">👤 Profile</a>
            
            <div class="nav-section">AI Tools</div>
            <a href="/saas/seo" class="<?= ($activeService ?? '')==='seo'?'active':'' ?>">🔍 SEO Writer</a>
            <a href="/saas/copy" class="<?= ($activeService ?? '')==='copy'?'active':'' ?>">✍️ Copywriter</a>
            <a href="/saas/images" class="<?= ($activeService ?? '')==='images'?'active':'' ?>">🖼️ Image Studio</a>
            <a href="/saas/blog" class="<?= ($activeService ?? '')==='blog'?'active':'' ?>">📝 Blog Writer</a>
            <a href="/saas/email" class="<?= ($activeService ?? '')==='email'?'active':'' ?>">📧 Email Creator</a>
            <a href="/saas/social" class="<?= ($activeService ?? '')==='social'?'active':'' ?>">📱 Social Manager</a>
            <a href="/saas/chat" class="<?= ($activeService ?? '')==='chat'?'active':'' ?>">💬 Chatbot Builder</a>
            <a href="/saas/landing" class="<?= ($activeService ?? '')==='landing'?'active':'' ?>">🎯 Landing Pages</a>
            <a href="/saas/website" class="<?= ($activeService ?? '')==='website'?'active':'' ?>">🌐 Website Builder</a>
            <a href="/saas/bizplan" class="<?= ($activeService ?? '')==='bizplan'?'active':'' ?>">📊 Business Plans</a>
            
            <div class="nav-section">Account</div>
            <a href="/saas/billing">💳 Billing</a>
            <a href="/saas/api-keys">🔑 API Keys</a>
            <a href="/saas/logout">🚪 Logout</a>
        </nav>
        <div class="saas-user">
            <div class="saas-user-info"><?= htmlspecialchars($saasName) ?></div>
            <div class="saas-user-plan"><?= htmlspecialchars($saasPlan) ?> plan</div>
        </div>
    </aside>
    <main class="saas-main">
        <?= $content ?? '' ?>
    </main>
</div>
</body>
</html>
