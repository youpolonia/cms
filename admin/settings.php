<?php
/**
 * General Settings - Modern Dark UI
 */
define('CMS_ROOT', dirname(__DIR__));
require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session


require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
require_once CMS_ROOT . '/core/settings_general.php';
require_once CMS_ROOT . '/models/settingsmodel.php';

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$settings = general_settings_get();
$settingsModel = new SettingsModel();
$apiKeys = [
    'pexels_api_key' => $settingsModel->getValue('pexels_api_key', ''),
];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    // Handle API Keys form
    if (isset($_POST['save_api_keys'])) {
        $settingsModel->set('pexels_api_key', trim($_POST['pexels_api_key'] ?? ''), 'integrations');
        header('Location: settings.php?saved=2');
        exit;
    }
    
    // Handle General Settings form
    general_settings_update([
        'site_name' => trim($_POST['site_name'] ?? ''),
        'contact_email' => trim($_POST['contact_email'] ?? ''),
        'timezone' => trim($_POST['timezone'] ?? 'UTC'),
        'homepage_title_suffix' => trim($_POST['homepage_title_suffix'] ?? ''),
        'homepage_description' => trim($_POST['homepage_description'] ?? '')
    ]);
    header('Location: settings.php?saved=1');
    exit;
}

if (isset($_GET['saved'])) {
    $msg = $_GET['saved'] == '2' ? 'API keys saved successfully.' : 'Settings saved successfully.';
    // Reload API keys after save
    $apiKeys = [
        'pexels_api_key' => $settingsModel->getValue('pexels_api_key', ''),
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings - CMS Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--bg:#181825;--bg2:#1e1e2e;--bg3:#313244;--bg4:#45475a;--text:#cdd6f4;--text2:#a6adc8;--muted:#6c7086;--accent:#89b4fa;--success:#a6e3a1;--warning:#f9e2af;--danger:#f38ba8;--purple:#cba6f7;--border:#313244;--bg-secondary:#1e1e2e;--bg-tertiary:#313244;--border-color:#313244;--text-primary:#cdd6f4;--accent-color:#89b4fa;--text-muted:#6c7086;--bg-elevated:#313244;--bg-hover:#45475a;--success-color:#a6e3a1;--warning-color:#f9e2af;--danger-color:#f38ba8}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px;line-height:1.6;min-height:100vh}
.container{max-width:800px;margin:0 auto;padding:24px 32px}
.card{background:var(--bg2);border:1px solid var(--border);border-radius:16px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.card-title{font-size:15px;font-weight:600}
.card-body{padding:24px}
.alert{padding:14px 18px;border-radius:10px;margin-bottom:20px;display:flex;gap:10px}
.alert-success{background:rgba(166,227,161,.15);border:1px solid rgba(166,227,161,.3);color:var(--success)}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:500;margin-bottom:8px;color:var(--text2)}
.form-group input,.form-group textarea{width:100%;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;color:var(--text);font-size:14px;transition:.15s}
.form-group input:focus,.form-group textarea:focus{outline:none;border-color:var(--accent);box-shadow:0 0 0 3px rgba(137,180,250,.15)}
.form-group textarea{resize:vertical;min-height:100px}
.form-group small{display:block;margin-top:6px;font-size:12px;color:var(--muted)}
.btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;font-size:14px;font-weight:600;border:none;border-radius:10px;cursor:pointer;transition:.15s}
.btn-primary{background:var(--accent);color:#000}
.btn-primary:hover{background:var(--purple)}
.info{margin-top:20px;padding:14px;background:var(--bg);border-radius:10px;font-size:12px;color:var(--muted)}
.info code{background:var(--bg3);padding:2px 6px;border-radius:4px}
</style>
</head>
<body>
<?php require_once CMS_ROOT . '/admin/includes/topbar_nav.php'; ?>

<?php
$pageHeader = [
    'icon' => '⚙️',
    'title' => 'General Settings',
    'description' => 'Site configuration',
    'back_url' => '/admin',
    'back_text' => 'Dashboard',
    'gradient' => 'var(--purple), var(--accent-color)',
];
require_once CMS_ROOT . '/admin/includes/page_header.php';
?>

<div class="container">

<?php if ($msg): ?>
<div class="alert alert-success"><span>✅</span><span><?= esc($msg) ?></span></div>
<?php endif; ?>

<div class="card">
<div class="card-head"><span>🌐</span><span class="card-title">Site Settings</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>

<div class="form-group">
<label>Site Name</label>
<input type="text" name="site_name" value="<?= esc($settings['site_name']) ?>" placeholder="My CMS Site">
</div>

<div class="form-group">
<label>Contact Email</label>
<input type="email" name="contact_email" value="<?= esc($settings['contact_email']) ?>" placeholder="admin@example.com">
</div>

<div class="form-group">
<label>Timezone</label>
<input type="text" name="timezone" value="<?= esc($settings['timezone']) ?>" placeholder="UTC">
<small>Examples: UTC, America/New_York, Europe/London, Europe/Warsaw</small>
</div>

<div class="form-group">
<label>Homepage Title Suffix</label>
<input type="text" name="homepage_title_suffix" value="<?= esc($settings['homepage_title_suffix']) ?>" placeholder="| My CMS">
</div>

<div class="form-group">
<label>Homepage Description</label>
<textarea name="homepage_description" placeholder="A powerful custom CMS..."><?= esc($settings['homepage_description']) ?></textarea>
</div>

<button type="submit" class="btn btn-primary">💾 Save Settings</button>
</form>

<div class="info">Settings stored in <code>config/general_settings.json</code></div>
</div>
</div>

<!-- Integrations / API Keys -->
<div class="card" style="margin-top:24px;">
<div class="card-head"><span>🔗</span><span class="card-title">Integrations / API Keys</span></div>
<div class="card-body">
<form method="POST">
<?php csrf_field(); ?>
<input type="hidden" name="save_api_keys" value="1">

<div class="form-group">
<label>Pexels API Key</label>
<input type="text" name="pexels_api_key" value="<?= esc($apiKeys['pexels_api_key']) ?>" placeholder="Enter your Pexels API key">
<small>Free stock videos in Theme Builder. Get key at <a href="https://www.pexels.com/api/" target="_blank" style="color:var(--accent)">pexels.com/api</a></small>
</div>

<button type="submit" class="btn btn-primary">💾 Save API Keys</button>
</form>

<div class="info">API keys are stored securely in database <code>settings</code> table</div>
</div>
</div>

</div>
</body>
</html>
