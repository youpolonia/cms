<?php
/**
 * Themes Manager - Select and manage active theme
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

// Theme management available to admins

function esc($str) {
    return htmlspecialchars((string)$str, ENT_QUOTES, 'UTF-8');
}

function get_active_theme_local(): string {
    $configPath = CMS_ROOT . '/config_core/theme.php';
    if (file_exists($configPath)) {
        $config = @include $configPath;
        if (is_array($config) && isset($config['active_theme'])) {
            return (string)$config['active_theme'];
        }
    }
    return 'default';
}

function set_active_theme_local(string $slug): bool {
    $configPath = CMS_ROOT . '/config_core/theme.php';
    $themePath = CMS_ROOT . '/themes/' . $slug;
    
    if (!is_dir($themePath)) {
        return false;
    }
    
    $config = [];
    if (file_exists($configPath)) {
        $config = @include $configPath;
        if (!is_array($config)) {
            $config = [];
        }
    }
    
    $config['active_theme'] = $slug;
    $content = "<?php\nreturn " . var_export($config, true) . ";\n";
    
    return file_put_contents($configPath, $content, LOCK_EX) !== false;
}

function delete_theme_local(string $slug): bool {
    $themePath = CMS_ROOT . '/themes/' . $slug;
    $themesDir = CMS_ROOT . '/themes';
    
    // Security: verify path is within themes directory
    $realThemesDir = realpath($themesDir);
    $realThemePath = realpath($themePath);
    
    if (!$realThemePath || !str_starts_with($realThemePath, $realThemesDir . DIRECTORY_SEPARATOR)) {
        return false;
    }
    
    if (!is_dir($realThemePath)) {
        return false;
    }
    
    return delete_directory_recursive($realThemePath);
}

function delete_directory_recursive(string $dir): bool {
    if (!is_dir($dir)) {
        return false;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (is_dir($path)) {
            delete_directory_recursive($path);
        } else {
            @unlink($path);
        }
    }
    
    return @rmdir($dir);
}

function is_theme_protected(string $slug): bool {
    $protected = ['jessie', 'default', 'default_public', 'core', 'presets', 'current'];
    return in_array($slug, $protected);
}

function list_themes_local(): array {
    $themes = [];
    $themesPath = CMS_ROOT . '/themes';
    
    if (!is_dir($themesPath)) {
        return $themes;
    }
    
    $dirs = @scandir($themesPath);
    if (!$dirs) {
        return $themes;
    }
    
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..' || $dir === 'README.md' || $dir === 'core' || $dir === 'presets') {
            continue;
        }
        
        $themePath = $themesPath . '/' . $dir;
        if (!is_dir($themePath)) {
            continue;
        }
        
        $metadata = [];
        $jsonPath = $themePath . '/theme.json';
        if (file_exists($jsonPath)) {
            $json = @file_get_contents($jsonPath);
            if ($json) {
                $metadata = json_decode($json, true) ?: [];
            }
        }
        
        $themes[] = [
            'slug' => $dir,
            'name' => $metadata['name'] ?? ucfirst($dir),
            'description' => $metadata['description'] ?? '',
            'version' => $metadata['version'] ?? '1.0.0',
            'author' => $metadata['author'] ?? '',
        ];
    }
    
    usort($themes, fn($a, $b) => strcasecmp($a['name'], $b['name']));
    return $themes;
}

$message = '';
$messageType = '';
$activeTheme = get_active_theme_local();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    
    $action = $_POST['action'] ?? '';
    $slug = $_POST['theme'] ?? '';
    
    if ($action === 'activate' && !empty($slug)) {
        if (preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            if (set_active_theme_local($slug)) {
                $activeTheme = $slug;
                $message = "Theme '$slug' activated successfully!";
                $messageType = 'success';
            } else {
                $message = 'Failed to activate theme. Check file permissions.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid theme name';
            $messageType = 'error';
        }
    }
    
    if ($action === 'delete' && !empty($slug)) {
        if (!preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            $message = 'Invalid theme name';
            $messageType = 'error';
        } elseif (is_theme_protected($slug)) {
            $message = "Theme '$slug' is protected and cannot be deleted.";
            $messageType = 'error';
        } elseif ($slug === $activeTheme) {
            $message = 'Cannot delete the active theme. Please activate another theme first.';
            $messageType = 'error';
        } elseif (delete_theme_local($slug)) {
            $message = "Theme '$slug' deleted successfully!";
            $messageType = 'success';
        } else {
            $message = 'Failed to delete theme. Check file permissions.';
            $messageType = 'error';
        }
    }
}

$themes = list_themes_local();
$themesDir = CMS_ROOT . '/themes';

$pageTitle = 'Themes';
require_once CMS_ROOT . '/admin/includes/header.php';
?>

<style>
.themes-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.themes-stats { display: flex; gap: 1rem; }
.stat-box { background: var(--card-bg); border: 1px solid var(--border); border-radius: 8px; padding: 0.75rem 1rem; text-align: center; }
.stat-box .value { font-size: 1.5rem; font-weight: 700; color: var(--primary); }
.stat-box .label { font-size: 0.75rem; color: var(--text-muted); }

.themes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
.theme-card { background: var(--card-bg); border: 2px solid var(--border); border-radius: 12px; overflow: hidden; transition: all 0.2s; position: relative; }
.theme-card:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.theme-card.active { border-color: var(--success); }
.theme-card.active::before { content: 'Active'; position: absolute; top: 1rem; right: 1rem; background: var(--success); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; z-index: 10; }

.theme-preview { height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
.theme-preview img { width: 100%; height: 100%; object-fit: cover; }
.theme-preview .placeholder { font-size: 4rem; opacity: 0.3; }
.theme-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.5); opacity: 0; transition: opacity 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.75rem; }
.theme-card:hover .theme-overlay { opacity: 1; }
.theme-overlay a { padding: 0.5rem 1rem; background: white; color: var(--text); border-radius: 6px; text-decoration: none; font-size: 0.8125rem; font-weight: 500; }
.theme-overlay a:hover { background: var(--primary); color: white; }

.theme-info { padding: 1.25rem; }
.theme-name { font-weight: 600; font-size: 1.125rem; margin-bottom: 0.25rem; }
.theme-desc { font-size: 0.8125rem; color: var(--text-muted); margin-bottom: 0.75rem; line-height: 1.5; min-height: 40px; }
.theme-meta { display: flex; gap: 1rem; font-size: 0.75rem; color: var(--text-muted); }

.theme-footer { padding: 1rem 1.25rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.5rem; }
.btn-danger { background: #ef4444; color: white; border: none; }
.btn-danger:hover { background: #dc2626; }

.info-box { background: #dbeafe; border: 1px solid #93c5fd; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; }
.info-box p { margin: 0; font-size: 0.875rem; color: #1e40af; }
</style>

<?php if ($message): ?>
<div class="alert alert-<?= $messageType ?>">
    <?= esc($message) ?>
</div>
<?php endif; ?>

<div class="info-box">
    <p>Themes control the visual appearance of your website. The active theme is used for all public pages.</p>
</div>

<div class="themes-header">
    <h2>Available Themes</h2>
    <div class="themes-stats">
        <div class="stat-box">
            <div class="value"><?= count($themes) ?></div>
            <div class="label">Themes</div>
        </div>
        <div class="stat-box">
            <div class="value"><?= esc($activeTheme) ?></div>
            <div class="label">Active</div>
        </div>
    </div>
</div>

<?php if (empty($themes)): ?>
<div style="text-align: center; padding: 3rem; background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px;">
    <div style="font-size: 3rem; margin-bottom: 1rem;">No themes found</div>
    <p style="color: var(--text-muted);">Create a theme folder in /themes with a theme.json file.</p>
</div>
<?php else: ?>
<div class="themes-grid">
    <?php foreach ($themes as $theme): 
        $isActive = ($theme['slug'] === $activeTheme);
        $themeDir = $themesDir . '/' . $theme['slug'];
        
        $screenshot = null;
        foreach (['screenshot.png', 'screenshot.jpg', 'preview.png', 'preview.jpg'] as $img) {
            if (file_exists($themeDir . '/' . $img)) {
                $screenshot = '/themes/' . $theme['slug'] . '/' . $img;
                break;
            }
        }
    ?>
    <div class="theme-card <?= $isActive ? 'active' : '' ?>">
        <div class="theme-preview">
            <?php if ($screenshot): ?>
            <img src="<?= esc($screenshot) ?>" alt="<?= esc($theme['name']) ?>">
            <?php else: ?>
            <span class="placeholder">T</span>
            <?php endif; ?>
            <div class="theme-overlay">
                <a href="/admin/theme-editor/<?= urlencode($theme['slug']) ?>">Edit</a>
                <a href="/?theme=<?= urlencode($theme['slug']) ?>" target="_blank">Preview</a>
            </div>
        </div>
        <div class="theme-info">
            <div class="theme-name"><?= esc($theme['name']) ?></div>
            <div class="theme-desc"><?= esc($theme['description'] ?: 'No description available.') ?></div>
            <div class="theme-meta">
                <span>v<?= esc($theme['version']) ?></span>
                <?php if (!empty($theme['author'])): ?>
                <span>by <?= esc($theme['author']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="theme-footer">
            <?php if ($isActive): ?>
            <button class="btn btn-secondary btn-sm" disabled>Active Theme</button>
            <?php else: ?>
            <form method="POST" style="margin: 0; display: inline;">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="activate">
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn primary btn-sm">Activate</button>
            </form>
            <?php if (!is_theme_protected($theme['slug'])): ?>
            <form method="POST" style="margin: 0; display: inline;" onsubmit="return confirm('Are you sure you want to delete theme \'<?= esc($theme['slug']) ?>\'? This cannot be undone.');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="theme" value="<?= esc($theme['slug']) ?>">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once CMS_ROOT . '/admin/includes/footer.php'; ?>
