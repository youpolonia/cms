<?php
/**
 * Plugins Marketplace - Manage CMS plugins
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', realpath(__DIR__ . '/..'));
}

require_once CMS_ROOT . '/config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');

require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');

require_once CMS_ROOT . '/admin/includes/permissions.php';
cms_require_admin_role();

if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    exit('Forbidden - DEV_MODE required');
}

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

$pluginsDir = CMS_ROOT . '/plugins';
$installedFile = CMS_ROOT . '/config/installed_plugins.json';

// Ensure installed_plugins.json exists
if (!file_exists($installedFile)) {
    @file_put_contents($installedFile, '{}');
}

/**
 * Get installed plugins list
 */
function get_installed_plugins(): array {
    global $installedFile;
    if (!file_exists($installedFile)) {
        return [];
    }
    $content = @file_get_contents($installedFile);
    if ($content === false) {
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Save installed plugins list
 */
function save_installed_plugins(array $plugins): array {
    global $installedFile;
    
    // Check if file exists and is writable (skip directory check if file exists)
    if (file_exists($installedFile)) {
        if (!is_writable($installedFile)) {
            return ['success' => false, 'error' => "File not writable: $installedFile"];
        }
    } else {
        // File doesn't exist - check if directory is writable
        $dir = dirname($installedFile);
        if (!is_writable($dir)) {
            return ['success' => false, 'error' => "Directory not writable: $dir"];
        }
    }
    
    $json = json_encode($plugins, JSON_PRETTY_PRINT);
    if ($json === false) {
        return ['success' => false, 'error' => 'JSON encode failed'];
    }
    
    $result = @file_put_contents($installedFile, $json, LOCK_EX);
    if ($result === false) {
        $err = error_get_last();
        return ['success' => false, 'error' => 'Write failed: ' . ($err['message'] ?? 'unknown')];
    }
    
    return ['success' => true];
}

/**
 * Scan available plugins
 */
function scan_plugins(): array {
    global $pluginsDir;
    $plugins = [];
    
    if (!is_dir($pluginsDir)) {
        return $plugins;
    }
    
    $dirs = @scandir($pluginsDir);
    if (!$dirs) {
        return $plugins;
    }
    
    foreach ($dirs as $slug) {
        if ($slug === '.' || $slug === '..') {
            continue;
        }
        
        $dir = $pluginsDir . '/' . $slug;
        if (!is_dir($dir)) {
            continue;
        }
        
        $metaFile = $dir . '/plugin.json';
        
        if (file_exists($metaFile)) {
            $content = @file_get_contents($metaFile);
            $meta = $content ? json_decode($content, true) : null;
            if ($meta && is_array($meta)) {
                $plugins[$slug] = array_merge([
                    'slug' => $slug,
                    'name' => $slug,
                    'version' => '1.0.0',
                    'description' => '',
                    'author' => '',
                    'requires' => '1.0.0',
                ], $meta);
            }
        } else {
            // Plugin without metadata - still list it
            $plugins[$slug] = [
                'slug' => $slug,
                'name' => ucfirst(str_replace(['-', '_'], ' ', $slug)),
                'version' => '1.0.0',
                'description' => 'No plugin.json found',
                'author' => 'Unknown',
            ];
        }
    }
    
    ksort($plugins);
    return $plugins;
}

/**
 * Check if plugin is installed/active
 */
function is_plugin_installed(string $slug): bool {
    $installed = get_installed_plugins();
    return isset($installed[$slug]) && ($installed[$slug]['active'] ?? false) === true;
}

/**
 * Install plugin
 */
function install_plugin(string $slug): array {
    global $pluginsDir;
    
    $pluginDir = $pluginsDir . '/' . $slug;
    if (!is_dir($pluginDir)) {
        return ['success' => false, 'error' => "Plugin directory not found: $slug"];
    }
    
    // Run install script if exists (but don't fail if it errors)
    $installScript = $pluginDir . '/install.php';
    if (file_exists($installScript)) {
        try {
            require_once $installScript;
        } catch (Throwable $e) {
            error_log("Plugin install script error ($slug): " . $e->getMessage());
            // Continue anyway - the script error shouldn't block installation
        }
    }
    
    $installed = get_installed_plugins();
    $installed[$slug] = [
        'active' => true,
        'installed_at' => date('Y-m-d H:i:s'),
    ];
    
    return save_installed_plugins($installed);
}

/**
 * Upload and extract plugin ZIP
 */
function upload_plugin_zip(array $file): array {
    global $pluginsDir;
    
    // Validate upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed with error code: ' . $file['error']];
    }
    
    // Check MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'])) {
        return ['success' => false, 'error' => 'Invalid file type. Only ZIP files allowed. Got: ' . $mimeType];
    }
    
    // Check extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'zip') {
        return ['success' => false, 'error' => 'Invalid extension. Only .zip files allowed.'];
    }
    
    // Extract ZIP
    $zip = new ZipArchive();
    if ($zip->open($file['tmp_name']) !== true) {
        return ['success' => false, 'error' => 'Failed to open ZIP file'];
    }
    
    // Find plugin folder name (first directory in ZIP)
    $pluginSlug = null;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        if (strpos($name, '/') !== false) {
            $pluginSlug = explode('/', $name)[0];
            break;
        }
    }
    
    if (!$pluginSlug) {
        // Use filename without extension
        $pluginSlug = pathinfo($file['name'], PATHINFO_FILENAME);
    }
    
    // Sanitize slug
    $pluginSlug = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($pluginSlug));
    
    // Check if plugin already exists
    $targetDir = $pluginsDir . '/' . $pluginSlug;
    if (is_dir($targetDir)) {
        $zip->close();
        return ['success' => false, 'error' => 'Plugin already exists: ' . $pluginSlug];
    }
    
    // Extract to plugins directory
    if (!$zip->extractTo($pluginsDir)) {
        $zip->close();
        return ['success' => false, 'error' => 'Failed to extract ZIP'];
    }
    
    $zip->close();
    
    // Verify plugin.json exists
    if (!file_exists($targetDir . '/plugin.json')) {
        // Try to find it in subdirectory
        $dirs = glob($pluginsDir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            if (file_exists($dir . '/plugin.json') && basename($dir) !== $pluginSlug) {
                // Rename to proper slug
                rename($dir, $targetDir);
                break;
            }
        }
    }
    
    return ['success' => true, 'slug' => $pluginSlug];
}

/**
 * Uninstall plugin
 */
function uninstall_plugin(string $slug): array {
    global $pluginsDir;
    
    $pluginDir = $pluginsDir . '/' . $slug;
    
    // Run uninstall script if exists
    $uninstallScript = $pluginDir . '/uninstall.php';
    if (file_exists($uninstallScript)) {
        try {
            require_once $uninstallScript;
        } catch (Throwable $e) {
            error_log("Plugin uninstall script error ($slug): " . $e->getMessage());
        }
    }
    
    $installed = get_installed_plugins();
    unset($installed[$slug]);
    
    return save_installed_plugins($installed);
}

// Handle actions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $action = $_POST['action'] ?? '';
    $slug = $_POST['plugin'] ?? '';
    
    if (!preg_match('/^[a-z0-9_-]+$/i', $slug)) {
        $message = 'Invalid plugin name';
        $messageType = 'error';
    } else {
        switch ($action) {
            case 'install':
                $result = install_plugin($slug);
                if ($result['success']) {
                    $message = "Plugin '$slug' installed successfully!";
                    $messageType = 'success';
                } else {
                    $message = 'Failed to install plugin: ' . ($result['error'] ?? 'Unknown error');
                    $messageType = 'error';
                }
                break;
                
            case 'uninstall':
                $result = uninstall_plugin($slug);
                if ($result['success']) {
                    $message = "Plugin '$slug' uninstalled successfully!";
                    $messageType = 'success';
                } else {
                    $message = 'Failed to uninstall plugin: ' . ($result['error'] ?? 'Unknown error');
                    $messageType = 'error';
                }
                break;
                
            case 'upload':
                if (!empty($_FILES['plugin_zip'])) {
                    $result = upload_plugin_zip($_FILES['plugin_zip']);
                    if ($result['success']) {
                        $message = "Plugin uploaded successfully: " . $result['slug'];
                        $messageType = 'success';
                    } else {
                        $message = 'Upload failed: ' . ($result['error'] ?? 'Unknown error');
                        $messageType = 'error';
                    }
                } else {
                    $message = 'No file uploaded';
                    $messageType = 'error';
                }
                break;
        }
    }
}

$plugins = scan_plugins();
$installed = get_installed_plugins();

$pageTitle = 'Plugins';
require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.plugins-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.plugins-header h2 { margin: 0; }
.plugins-stats { display: flex; gap: 1rem; }
.stat-box { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem 1rem; text-align: center; }
.stat-box .value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
.stat-box .label { font-size: 0.75rem; color: var(--text-muted); }

.plugins-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.plugin-card { background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: all 0.2s; }
.plugin-card:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.plugin-card.installed { border-left: 4px solid var(--success); }
.plugin-header { padding: 1.25rem; border-bottom: 1px solid var(--border); }
.plugin-name { font-weight: 600; font-size: 1rem; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.5rem; }
.plugin-meta { font-size: 0.75rem; color: var(--text-muted); }
.plugin-body { padding: 1rem 1.25rem; min-height: 60px; }
.plugin-desc { font-size: 0.875rem; color: var(--text); line-height: 1.5; }
.plugin-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.plugin-status { font-size: 0.75rem; font-weight: 500; }
.plugin-status.active { color: var(--success); }
.plugin-status.inactive { color: var(--text-muted); }

.empty-state { text-align: center; padding: 3rem; background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px; }

.info-box { background: #dbeafe; border: 1px solid #93c5fd; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; }
.info-box p { margin: 0; font-size: 0.875rem; color: #1e40af; }

.debug-info { background: #fef3c7; border: 1px solid #fcd34d; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.8125rem; }
.debug-info code { background: rgba(0,0,0,0.1); padding: 0.125rem 0.375rem; border-radius: 3px; }
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= esc($message) ?>
</div>
<?php endif; ?>

<?php 
// Debug info in DEV_MODE - only show if file is not writable
$fileWritable = is_writable($installedFile);
if (!$fileWritable): 
?>
<div class="debug-info">
    <strong>Permission Issue Detected:</strong><br>
    File not writable: <code><?= esc($installedFile) ?></code><br>
    <br>
    <strong>Fix with:</strong><br>
    <code>sudo chown www-data:www-data <?= esc($installedFile) ?></code><br>
    <code>sudo chmod 664 <?= esc($installedFile) ?></code>
</div>
<?php endif; ?>

<div class="info-box">
    <p>Plugins extend your CMS with additional features. Upload a ZIP file, place plugin folders in <code>/plugins</code> directory, or use AI to build a new plugin.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
    <div class="card" style="margin-bottom: 0;">
        <h3 style="margin-bottom: 1rem;">📤 Upload Plugin</h3>
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="upload">
            <input type="hidden" name="plugin" value="upload">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; margin-bottom: 0.5rem; color: var(--text-muted);">Plugin ZIP File</label>
                <input type="file" name="plugin_zip" accept=".zip" required 
                       style="width: 100%; padding: 0.5rem; border: 1px solid var(--border); border-radius: 6px; background: var(--card-bg);">
            </div>
            <button type="submit" class="btn primary">📤 Upload & Install</button>
        </form>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem;">
            ZIP must contain a folder with <code>plugin.json</code> file. Max size: <?= ini_get('upload_max_filesize') ?>
        </p>
    </div>
    
    <div class="card" style="margin-bottom: 0; background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(139,92,246,0.1));">
        <h3 style="margin-bottom: 1rem;">🔧 Build Plugin with AI</h3>
        <p style="font-size: 0.875rem; color: var(--text); margin-bottom: 1rem;">
            Use AI to generate a complete plugin structure with admin pages, database tables, and API endpoints.
        </p>
        <a href="/admin/ai-plugin-builder.php" class="btn primary" style="background: linear-gradient(135deg, var(--primary), #8b5cf6);">
            🤖 Open AI Plugin Builder
        </a>
        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.75rem;">
            Describe your plugin and let AI write the code for you.
        </p>
    </div>
</div>

<div class="plugins-header">
    <h2>Available Plugins</h2>
    <div class="plugins-stats">
        <div class="stat-box">
            <div class="value"><?= count($plugins) ?></div>
            <div class="label">Total</div>
        </div>
        <div class="stat-box">
            <div class="value"><?= count(array_filter($installed, fn($p) => $p['active'] ?? false)) ?></div>
            <div class="label">Active</div>
        </div>
    </div>
</div>

<?php if (empty($plugins)): ?>
<div class="empty-state">
    <h3>No Plugins Found</h3>
    <p style="color: var(--text-muted);">Create a plugin folder in <code>/plugins</code> with a <code>plugin.json</code> file.</p>
</div>
<?php else: ?>
<div class="plugins-grid">
    <?php foreach ($plugins as $slug => $plugin): 
        $isInstalled = is_plugin_installed($slug);
    ?>
    <div class="plugin-card <?= $isInstalled ? 'installed' : '' ?>">
        <div class="plugin-header">
            <div class="plugin-name">
                <?= esc($plugin['name']) ?>
            </div>
            <div class="plugin-meta">
                v<?= esc($plugin['version']) ?>
                <?php if (!empty($plugin['author'])): ?>
                &bull; <?= esc($plugin['author']) ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="plugin-body">
            <p class="plugin-desc"><?= esc($plugin['description'] ?: 'No description available.') ?></p>
        </div>
        <div class="plugin-footer">
            <span class="plugin-status <?= $isInstalled ? 'active' : 'inactive' ?>">
                <?= $isInstalled ? 'Active' : 'Inactive' ?>
            </span>
            <form method="POST" style="margin: 0;">
                <?= csrf_field() ?>
                <input type="hidden" name="plugin" value="<?= esc($slug) ?>">
                <?php if ($isInstalled): ?>
                <input type="hidden" name="action" value="uninstall">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Uninstall this plugin?')">Uninstall</button>
                <?php else: ?>
                <input type="hidden" name="action" value="install">
                <button type="submit" class="btn primary btn-sm">Install</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card" style="margin-top: 2rem;">
    <h3>Creating a Plugin</h3>
    <p style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 1rem;">
        To create a plugin, make a folder in <code>/plugins</code> with the following structure:
    </p>
    <pre style="background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: 0.8125rem; overflow-x: auto;">/plugins/my-plugin/
├── plugin.json      # Required: Plugin metadata
├── index.php        # Main plugin file
├── install.php      # Optional: Runs on install
└── uninstall.php    # Optional: Runs on uninstall</pre>
    
    <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 1rem;">
        Example <code>plugin.json</code>:
    </p>
    <pre style="background: #1e293b; color: #e2e8f0; padding: 1rem; border-radius: 8px; font-size: 0.8125rem; overflow-x: auto;">{
    "name": "My Plugin",
    "version": "1.0.0",
    "description": "A sample plugin",
    "author": "Your Name"
}</pre>
</div>

<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
