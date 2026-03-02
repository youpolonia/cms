<?php
/**
 * SaaS Frontend Router — /saas/* (public-facing SaaS app)
 */
if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/db.php';
require_once __DIR__ . '/includes/class-saas-auth.php';
require_once __DIR__ . '/includes/class-saas-credits.php';

use Plugins\JessieSaasCore\SaasAuth;
use Plugins\JessieSaasCore\SaasCredits;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = preg_replace('#^/saas/?#', '', $uri);
$path = trim($path, '/');

// Session for SaaS
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('SAAS_SESSID');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
if (empty($_SESSION['saas_csrf'])) {
    $_SESSION['saas_csrf'] = bin2hex(random_bytes(16));
}
$csrfToken = $_SESSION['saas_csrf'];

// ── Public pages ──
switch ($path) {
    case 'login':
        if (SaasAuth::isLoggedIn()) { header('Location: /saas/dashboard', true, 303); exit; }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) { $error = 'Session expired, please try again.'; }
            else {
                $auth = new SaasAuth();
                $result = $auth->login($_POST['email'] ?? '', $_POST['password'] ?? '');
                if ($result['success']) { header('Location: /saas/dashboard', true, 303); exit; }
                $error = $result['error'];
            }
        }
        require __DIR__ . '/views/auth/login.php';
        exit;

    case 'register':
        if (SaasAuth::isLoggedIn()) { header('Location: /saas/dashboard', true, 303); exit; }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) { $error = 'Session expired, please try again.'; }
            else {
                $auth = new SaasAuth();
                $result = $auth->register($_POST['email'] ?? '', $_POST['password'] ?? '', $_POST['name'] ?? '', $_POST['company'] ?? '');
                if ($result['success']) {
                    $auth->login($_POST['email'], $_POST['password']);
                    header('Location: /saas/dashboard', true, 303); exit;
                }
                $error = $result['error'];
            }
        }
        require __DIR__ . '/views/auth/register.php';
        exit;
    
    case 'logout':
        $auth = new SaasAuth();
        $auth->logout();
        header('Location: /saas/login', true, 303);
        exit;
    
    case 'forgot-password':
        $error = '';
        $success = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
                $error = 'Session expired, please try again.';
            } else {
                $auth = new SaasAuth();
                $result = $auth->requestPasswordReset($_POST['email'] ?? '');
                if ($result['success']) {
                    $success = true;
                    // Send reset email if token was generated (user exists)
                    if (!empty($result['token'])) {
                        $resetUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
                            . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                            . '/saas/reset-password?token=' . $result['token'];
                        $toEmail = strtolower(trim($_POST['email'] ?? ''));
                        $subject = 'Password Reset — Jessie AI';
                        $body = "Hello,\n\n"
                            . "You requested a password reset for your Jessie AI account.\n\n"
                            . "Click this link to set a new password:\n"
                            . $resetUrl . "\n\n"
                            . "This link expires in 1 hour.\n\n"
                            . "If you didn't request this, please ignore this email.\n\n"
                            . "— Jessie AI";
                        $headers = "From: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n"
                            . "Content-Type: text/plain; charset=UTF-8";
                        @mail($toEmail, $subject, $body, $headers);
                    }
                }
            }
        }
        require __DIR__ . '/views/auth/forgot-password.php';
        exit;

    case 'reset-password':
        $error = '';
        $success = false;
        $resetToken = $_GET['token'] ?? $_POST['token'] ?? '';
        if (empty($resetToken)) {
            header('Location: /saas/forgot-password', true, 303);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!hash_equals($csrfToken, $_POST['csrf_token'] ?? '')) {
                $error = 'Session expired, please try again.';
            } else {
                $password = $_POST['password'] ?? '';
                $confirm = $_POST['password_confirm'] ?? '';
                if ($password !== $confirm) {
                    $error = 'Passwords do not match.';
                } elseif (strlen($password) < 8) {
                    $error = 'Password must be at least 8 characters.';
                } else {
                    $auth = new SaasAuth();
                    $result = $auth->resetPassword($resetToken, $password);
                    if ($result['success']) {
                        $success = true;
                    } else {
                        $error = $result['error'] ?? 'Reset failed. The link may have expired.';
                    }
                }
            }
        }
        require __DIR__ . '/views/auth/reset-password.php';
        exit;
}

// ── Protected pages (auth required) ──
SaasAuth::requireAuth();
$userId = SaasAuth::getUserId();

ob_start();
switch ($path) {
    case '':
    case 'dashboard':
        $activeService = 'dashboard';
        $credits = new SaasCredits();
        $subscriptions = $credits->getAllSubscriptions($userId);
        $auth = new SaasAuth();
        $user = $auth->getUser($userId);
        ?>
        <div class="saas-header"><h1>📊 Dashboard</h1></div>
        <div class="saas-content">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:32px">
                <div class="saas-card" style="text-align:center"><div style="font-size:32px;font-weight:700;color:#8b5cf6"><?= $user['credits_remaining'] ?? 0 ?></div><div style="color:#94a3b8;margin-top:4px">Credits Remaining</div></div>
                <div class="saas-card" style="text-align:center"><div style="font-size:32px;font-weight:700;color:#22c55e"><?= count($subscriptions) ?></div><div style="color:#94a3b8;margin-top:4px">Active Services</div></div>
                <div class="saas-card" style="text-align:center"><div style="font-size:32px;font-weight:700;color:#f59e0b"><?= strtoupper($user['plan'] ?? 'free') ?></div><div style="color:#94a3b8;margin-top:4px">Current Plan</div></div>
            </div>
            
            <h2 style="font-size:18px;font-weight:600;margin-bottom:16px">🛠️ Your AI Tools</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px">
                <?php
                $tools = [
                    ['🔍','SEO Writer','AI-powered SEO content optimization','/saas/seo','seowriter', true],
                    ['✍️','AI Copywriter','Product descriptions & ad copy','/saas/copy','copywriter', true],
                    ['🖼️','Image Studio','Background removal, enhancement, generation','/saas/images','imagestudio', true],
                    ['📱','Social Manager','AI social media posts & scheduling','/saas/social','socialmanager', true],
                    ['📧','Email Marketing','AI email campaigns & automation','/saas/email','emailcreator', true],
                    ['📊','Analytics','Website analytics & AI insights','/saas/analytics','analytics', true],
                    ['📝','Blog Writer','Full articles with research & SEO','/saas/blog','blogwriter', false],
                    ['💬','Chatbot Builder','AI chatbots for your website','/saas/chat','chatbot', false],
                    ['🎯','Landing Pages','High-converting pages in seconds','/saas/landing','landingpage', false],
                    ['🌐','Website Builder','Complete websites with AI','/saas/website','sitebuilder', false],
                ];
                foreach ($tools as [$icon, $name, $desc, $url, $svc, $available]): ?>
                <?php if ($available): ?>
                <a href="<?= $url ?>" style="text-decoration:none">
                    <div class="saas-card" style="transition:.15s;cursor:pointer" onmouseover="this.style.borderColor='#8b5cf6'" onmouseout="this.style.borderColor='#334155'">
                        <div style="font-size:28px;margin-bottom:8px"><?= $icon ?></div>
                        <div style="font-size:15px;font-weight:600;color:#e2e8f0"><?= $name ?></div>
                        <div style="font-size:12px;color:#94a3b8;margin-top:4px"><?= $desc ?></div>
                    </div>
                </a>
                <?php else: ?>
                <div class="saas-card" style="opacity:.5;position:relative">
                    <div style="position:absolute;top:8px;right:8px;background:#f59e0b20;color:#f59e0b;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600">COMING SOON</div>
                    <div style="font-size:28px;margin-bottom:8px"><?= $icon ?></div>
                    <div style="font-size:15px;font-weight:600;color:#e2e8f0"><?= $name ?></div>
                    <div style="font-size:12px;color:#94a3b8;margin-top:4px"><?= $desc ?></div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        break;
    
    case 'profile':
        $activeService = 'profile';
        $auth = new SaasAuth();
        $user = $auth->getUser($userId);
        ?>
        <div class="saas-header"><h1>👤 Profile</h1></div>
        <div class="saas-content">
            <div class="saas-card" style="max-width:600px">
                <div style="margin-bottom:20px"><label class="saas-label">Email</label><input class="saas-input" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled></div>
                <div style="margin-bottom:20px"><label class="saas-label">Name</label><input class="saas-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" id="profName"></div>
                <div style="margin-bottom:20px"><label class="saas-label">Company</label><input class="saas-input" value="<?= htmlspecialchars($user['company'] ?? '') ?>" id="profCompany"></div>
                <div style="margin-bottom:20px"><label class="saas-label">API Key</label><div style="display:flex;gap:8px"><input class="saas-input" value="<?= htmlspecialchars($user['api_key'] ?? '') ?>" readonly id="apiKey" style="font-family:monospace;font-size:12px"><button class="saas-btn saas-btn-ghost" onclick="navigator.clipboard.writeText(document.getElementById('apiKey').value)">📋</button></div></div>
                <button class="saas-btn saas-btn-primary" onclick="updateProfile()">Save Changes</button>
            </div>
        </div>
        <script>
        function updateProfile(){
            fetch('/api/saas/profile',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({name:document.getElementById('profName').value,company:document.getElementById('profCompany').value})}).then(r=>r.json()).then(d=>{if(d.success)alert('Profile updated!');});
        }
        </script>
        <?php
        break;
    
    case 'api-keys':
        $activeService = 'profile';
        $auth = new SaasAuth();
        $user = $auth->getUser($userId);
        ?>
        <div class="saas-header"><h1>🔑 API Keys</h1></div>
        <div class="saas-content">
            <div class="saas-card" style="max-width:700px">
                <h3 style="margin-bottom:12px">Your API Key</h3>
                <p style="color:#94a3b8;font-size:13px;margin-bottom:16px">Use this key in the <code style="background:#0f172a;padding:2px 6px;border-radius:4px">X-API-Key</code> header for all API requests.</p>
                <div style="background:#0f172a;padding:16px;border-radius:8px;font-family:monospace;font-size:13px;color:#22c55e;word-break:break-all;margin-bottom:16px"><?= htmlspecialchars($user['api_key'] ?? '') ?></div>
                <h3 style="margin-bottom:12px">Quick Start</h3>
                <pre style="background:#0f172a;padding:16px;border-radius:8px;font-size:12px;color:#94a3b8;overflow-x:auto">curl -X POST https://api.jessiecms.com/api/saas/seo/analyze \
  -H "X-API-Key: <?= htmlspecialchars($user['api_key'] ?? 'YOUR_KEY') ?>" \
  -H "Content-Type: application/json" \
  -d '{"url": "https://example.com", "keyword": "best coffee"}'</pre>
            </div>
        </div>
        <?php
        break;
        
    case 'billing':
        $activeService = 'profile';
        $credits = new SaasCredits();
        $subs = $credits->getAllSubscriptions($userId);
        ?>
        <div class="saas-header"><h1>💳 Billing & Subscriptions</h1></div>
        <div class="saas-content">
            <div class="saas-card">
                <h3 style="margin-bottom:16px">Active Subscriptions</h3>
                <table style="width:100%;border-collapse:collapse">
                    <thead><tr style="border-bottom:1px solid #334155"><th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Service</th><th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Plan</th><th style="text-align:right;padding:8px;color:#94a3b8;font-size:12px">Credits Used</th><th style="text-align:left;padding:8px;color:#94a3b8;font-size:12px">Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($subs as $s): ?>
                    <tr style="border-bottom:1px solid #1e293b">
                        <td style="padding:10px;color:#e2e8f0"><?= htmlspecialchars($s['service']) ?></td>
                        <td style="padding:10px"><span style="background:#8b5cf620;color:#8b5cf6;padding:2px 8px;border-radius:4px;font-size:12px"><?= htmlspecialchars($s['plan_name']) ?></span></td>
                        <td style="text-align:right;padding:10px;color:#f59e0b"><?= $s['credits_used'] ?> / <?= $s['credits_limit'] ?: '∞' ?></td>
                        <td style="padding:10px;color:#22c55e"><?= $s['status'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        break;
    
    // ── SaaS Tool Pages ──
    case 'seo':
        $activeService = 'seowriter';
        require CMS_ROOT . '/plugins/jessie-seowriter/views/app/editor.php';
        break;
    case 'copy':
        $activeService = 'copywriter';
        require CMS_ROOT . '/plugins/jessie-copywriter/views/app/generator.php';
        break;
    case 'images':
        $activeService = 'imagestudio';
        require CMS_ROOT . '/plugins/jessie-imagestudio/views/app/studio.php';
        break;
    case 'social':
        $activeService = 'socialmanager';
        require CMS_ROOT . '/plugins/jessie-social/views/app/scheduler.php';
        break;
    case 'email':
        $activeService = 'emailcreator';
        require CMS_ROOT . '/plugins/jessie-emailmarketing/views/app/campaigns.php';
        break;
    case 'analytics':
        $activeService = 'analytics';
        require CMS_ROOT . '/plugins/jessie-analytics/views/app/overview.php';
        break;

    default:
        http_response_code(404);
        ?>
        <div class="saas-header"><h1>🔍 Page Not Found</h1></div>
        <div class="saas-content"><div class="saas-card"><p style="color:#94a3b8">This tool is coming soon. <a href="/saas/dashboard" style="color:#8b5cf6">← Back to dashboard</a></p></div></div>
        <?php
}

$content = ob_get_clean();
require __DIR__ . '/views/app/layout.php';
