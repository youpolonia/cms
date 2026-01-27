<?php
/**
 * Session Diagnostic Tool - Access from browser
 * URL: http://your-server/admin/session_diag.php
 */
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('admin');

// Capture request info BEFORE any output
$diagData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'http_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'unknown',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
    'https_detected' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
                     || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
    'session_id' => session_id(),
    'session_name' => session_name(),
    'cookie_params' => session_get_cookie_params(),
    'cookies_received' => $_COOKIE,
    'session_contents' => $_SESSION,
    'headers_list' => headers_list(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Session Diagnostic</title>
<style>
body { font-family: monospace; background: #1a1a2e; color: #eee; padding: 20px; }
h1 { color: #89b4fa; }
h2 { color: #f9e2af; margin-top: 20px; }
pre { background: #313244; padding: 15px; border-radius: 8px; overflow-x: auto; }
.ok { color: #a6e3a1; }
.warn { color: #f9e2af; }
.err { color: #f38ba8; }
.section { margin: 20px 0; padding: 15px; background: #262640; border-radius: 8px; }
</style>
</head>
<body>
<h1>🔍 Session Diagnostic</h1>

<div class="section">
<h2>1. Cookie Status</h2>
<?php if (empty($_COOKIE)): ?>
<p class="err">❌ NO COOKIES RECEIVED - Browser is not sending any cookies!</p>
<p>Possible causes:</p>
<ul>
<li>Browser blocking third-party cookies</li>
<li>Browser privacy settings too strict</li>
<li>Domain mismatch (accessing via IP vs hostname)</li>
<li>SameSite policy issue</li>
</ul>
<?php elseif (!isset($_COOKIE['CMSSESSID_ADMIN'])): ?>
<p class="warn">⚠️ CMSSESSID_ADMIN cookie NOT found. Cookies received:</p>
<pre><?php print_r($_COOKIE); ?></pre>
<?php else: ?>
<p class="ok">✅ CMSSESSID_ADMIN cookie received: <?php echo htmlspecialchars($_COOKIE['CMSSESSID_ADMIN']); ?></p>
<?php endif; ?>
</div>

<div class="section">
<h2>2. Session Status</h2>
<p>Session ID: <strong><?php echo htmlspecialchars(session_id()); ?></strong></p>
<p>Session Name: <strong><?php echo htmlspecialchars(session_name()); ?></strong></p>
<p>Session Active: <?php echo session_status() === PHP_SESSION_ACTIVE ? '<span class="ok">YES</span>' : '<span class="err">NO</span>'; ?></p>
<p>Authenticated: <?php echo !empty($_SESSION['admin_authenticated']) ? '<span class="ok">YES - ' . htmlspecialchars($_SESSION['admin_username'] ?? 'unknown') . '</span>' : '<span class="warn">NO</span>'; ?></p>

<h3>Session Contents:</h3>
<pre><?php print_r($_SESSION); ?></pre>
</div>

<div class="section">
<h2>3. Cookie Parameters (Server-Side)</h2>
<pre><?php print_r($diagData['cookie_params']); ?></pre>
<p>HTTPS Detected: <?php echo $diagData['https_detected'] ? '<span class="ok">YES</span>' : '<span class="warn">NO (cookie Secure flag not set)</span>'; ?></p>
</div>

<div class="section">
<h2>4. Request Info</h2>
<p>HTTP Host: <strong><?php echo htmlspecialchars($diagData['http_host']); ?></strong></p>
<p>Server Name: <strong><?php echo htmlspecialchars($diagData['server_name']); ?></strong></p>
<p>Server Addr: <strong><?php echo htmlspecialchars($diagData['server_addr']); ?></strong></p>
<p>Remote Addr: <strong><?php echo htmlspecialchars($diagData['remote_addr']); ?></strong></p>
<p>User Agent: <small><?php echo htmlspecialchars($diagData['user_agent']); ?></small></p>
</div>

<div class="section">
<h2>5. Response Headers</h2>
<pre><?php
$headers = headers_list();
foreach ($headers as $h) {
    echo htmlspecialchars($h) . "\n";
}
?></pre>
</div>

<div class="section">
<h2>6. Browser Test</h2>
<p>Open browser DevTools (F12) → Network tab → Reload this page.</p>
<p>Check:</p>
<ul>
<li><strong>Request Headers:</strong> Is there a "Cookie: CMSSESSID_ADMIN=..." header?</li>
<li><strong>Response Headers:</strong> Is there a "Set-Cookie: CMSSESSID_ADMIN=..." header?</li>
</ul>
<p>If Set-Cookie is present but Cookie is not on reload, the browser is refusing to save the cookie.</p>
</div>

<div class="section">
<h2>7. Quick Actions</h2>
<p><a href="/admin/login.php" style="color: #89b4fa;">→ Go to Login Page</a></p>
<p><a href="/admin/index.php" style="color: #89b4fa;">→ Go to Dashboard</a></p>
<p><a href="javascript:location.reload()" style="color: #89b4fa;">→ Reload This Page</a></p>
</div>

</body>
</html>
