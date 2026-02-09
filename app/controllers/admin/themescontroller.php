<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

require_once CMS_ROOT . '/models/settingsmodel.php';
require_once CMS_ROOT . '/core/theme-installer.php';
require_once CMS_ROOT . '/core/theme-content.php';
require_once CMS_ROOT . '/core/cache.php';

use Core\Request;
use Core\Response;
use Core\Session;

class ThemesController
{
    private string $themesDir;
    private string $configPath;

    public function __construct()
    {
        $this->themesDir = \CMS_ROOT . '/themes';
        $this->configPath = \CMS_ROOT . '/config_core/theme.php';
    }

    public function index(Request $request): void
    {
        $themes = $this->listThemes();
        $activeTheme = $this->getActiveTheme();

        render('admin/themes/index', [
            'themes' => $themes,
            'activeTheme' => $activeTheme,
            'themesDir' => $this->themesDir,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function activate(Request $request): void
    {
        $slug = $request->post('theme', '');
        
        if (empty($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }

        $themePath = $this->themesDir . '/' . $slug;
        if (!is_dir($themePath)) {
            Session::flash('error', 'Theme directory not found.');
            Response::redirect('/admin/themes');
            return;
        }

        if ($this->setActiveTheme($slug)) {
            // Import JTB templates if theme has them
            $templatesDir = $themePath . '/templates';
            if (is_dir($templatesDir)) {
                $pdo = db();
                $importResult = jtb_install_theme($slug, $pdo);
                if ($importResult['success']) {
                    \Cache::clear('jtb_templates');
                }
            }
            \Cache::clear('system_settings');
            \Cache::clear('active_theme');
            \Cache::clear('theme_config');
            Session::flash('success', "Theme '{$slug}' activated successfully!");
        } else {
            Session::flash('error', 'Failed to activate theme. Check file permissions.');
        }

        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        Response::redirect('/admin/themes');
    }

    public function delete(Request $request): void
    {
        $slug = $request->post('theme', '');
        
        if (empty($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }

        $protected = ['jessie', 'default', 'default_public', 'core', 'presets', 'current'];
        if (in_array($slug, $protected)) {
            Session::flash('error', "Theme '{$slug}' is protected and cannot be deleted.");
            Response::redirect('/admin/themes');
            return;
        }

        $activeTheme = $this->getActiveTheme();
        if ($slug === $activeTheme) {
            Session::flash('error', 'Cannot delete the active theme. Please activate another theme first.');
            Response::redirect('/admin/themes');
            return;
        }

        $themePath = $this->themesDir . '/' . $slug;
        $realThemesDir = realpath($this->themesDir);
        $realThemePath = realpath($themePath);
        
        if (!$realThemePath || !str_starts_with($realThemePath, $realThemesDir . DIRECTORY_SEPARATOR)) {
            Session::flash('error', 'Invalid theme path.');
            Response::redirect('/admin/themes');
            return;
        }

        if (!is_dir($realThemePath)) {
            Session::flash('error', 'Theme directory not found.');
            Response::redirect('/admin/themes');
            return;
        }

        if ($this->deleteDirectory($realThemePath)) {
            Session::flash('success', "Theme '{$slug}' deleted successfully!");
        } else {
            Session::flash('error', 'Failed to delete theme. Check file permissions.');
        }

        Response::redirect('/admin/themes');
    }

    public function upload(Request $request): void
    {
        if (!isset($_FILES['theme_zip']) || $_FILES['theme_zip']['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            ];
            $errorCode = $_FILES['theme_zip']['error'] ?? UPLOAD_ERR_NO_FILE;
            Session::flash('error', $errorMessages[$errorCode] ?? 'Unknown upload error.');
            Response::redirect('/admin/themes');
            return;
        }

        $file = $_FILES['theme_zip'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        $allowedMimes = ['application/zip', 'application/x-zip-compressed', 'application/x-zip'];
        if (!in_array($mimeType, $allowedMimes)) {
            Session::flash('error', 'Invalid file type. Only ZIP files are allowed.');
            Response::redirect('/admin/themes');
            return;
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'zip') {
            Session::flash('error', 'Invalid file extension. Only .zip files are allowed.');
            Response::redirect('/admin/themes');
            return;
        }

        $tempDir = sys_get_temp_dir() . '/theme_upload_' . uniqid();
        if (!mkdir($tempDir, 0755, true)) {
            Session::flash('error', 'Failed to create temporary directory.');
            Response::redirect('/admin/themes');
            return;
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($file['tmp_name']) !== true) {
                throw new \Exception('Failed to open ZIP file.');
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (strpos($filename, '..') !== false) {
                    throw new \Exception('Invalid file path detected in ZIP.');
                }
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                $blocked = ['exe', 'bat', 'sh', 'cmd', 'com', 'pif', 'scr', 'vbs', 'jar'];
                if (in_array($ext, $blocked)) {
                    throw new \Exception('Blocked file type: .' . $ext);
                }
            }

            $zip->extractTo($tempDir);
            $zip->close();

            $themeDir = $this->findThemeDir($tempDir);
            if (!$themeDir) {
                throw new \Exception('Invalid theme. Must contain theme.json file.');
            }

            $themeJson = json_decode(file_get_contents($themeDir . '/theme.json'), true);
            if (!$themeJson || empty($themeJson['name'])) {
                throw new \Exception('Invalid theme.json - missing name field.');
            }

            $slug = basename($themeDir);
            if ($slug === basename($tempDir)) {
                $slug = preg_replace('/[^a-z0-9_-]/i', '-', strtolower($themeJson['name']));
                $slug = trim(preg_replace('/-+/', '-', $slug), '-');
            }

            $targetPath = $this->themesDir . '/' . $slug;
            if (is_dir($targetPath)) {
                $slug = $slug . '-' . date('Ymd');
                $targetPath = $this->themesDir . '/' . $slug;
            }

            if (!$this->copyDirectory($themeDir, $targetPath)) {
                throw new \Exception('Failed to install theme. Check permissions.');
            }

            $this->setPermissions($targetPath);
            Session::flash('success', "Theme '{$themeJson['name']}' installed as '{$slug}'!");

        } catch (\Exception $e) {
            Session::flash('error', 'Installation failed: ' . $e->getMessage());
        } finally {
            $this->deleteDirectory($tempDir);
        }

        Response::redirect('/admin/themes');
    }

    private function findThemeDir(string $dir): ?string
    {
        if (file_exists($dir . '/theme.json')) return $dir;
        $items = @scandir($dir);
        if ($items) {
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') continue;
                $subdir = $dir . '/' . $item;
                if (is_dir($subdir) && file_exists($subdir . '/theme.json')) return $subdir;
            }
        }
        return null;
    }

    private function copyDirectory(string $src, string $dst): bool
    {
        if (!is_dir($src)) return false;
        if (!is_dir($dst) && !mkdir($dst, 0755, true)) return false;
        $dir = opendir($src);
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') continue;
            $srcPath = $src . '/' . $file;
            $dstPath = $dst . '/' . $file;
            if (is_dir($srcPath)) {
                if (!$this->copyDirectory($srcPath, $dstPath)) { closedir($dir); return false; }
            } else {
                if (!copy($srcPath, $dstPath)) { closedir($dir); return false; }
            }
        }
        closedir($dir);
        return true;
    }

    private function setPermissions(string $dir): void
    {
        @chmod($dir, 0755);
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $item) {
            @chmod($item->getPathname(), $item->isDir() ? 0755 : 0644);
        }
    }

    private function deleteDirectory(string $dir): bool
    {
        if (!is_dir($dir)) return false;
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : @unlink($path);
        }
        return @rmdir($dir);
    }

    private function getActiveTheme(): string
    {
        return \SettingsModel::getActiveTheme();
    }


    /**
     * Install demo content for a theme
     */
    public function installDemo(Request $request): void
    {
        $slug = $request->post('theme', '');
        
        if (empty($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'error' => 'Invalid theme name']);
                return;
            }
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }
        
        if (!theme_has_demo_content($slug)) {
            if ($this->isAjax()) {
                Response::json(['success' => false, 'error' => 'No demo content available for this theme']);
                return;
            }
            Session::flash('error', 'No demo content available for this theme.');
            Response::redirect('/admin/themes');
            return;
        }
        
        $clearExisting = !empty($request->post('clear_existing', ''));
        $result = theme_install_demo_content($slug, [
            'clear_existing' => $clearExisting,
            'install_menu' => true
        ]);
        
        if ($this->isAjax()) {
            Response::json($result);
            return;
        }
        
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }
        Response::redirect('/admin/themes');
    }
    
    /**
     * Remove demo content for a theme
     */
    public function removeDemo(Request $request): void
    {
        $slug = $request->post('theme', '');
        
        if (empty($slug)) {
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }
        
        $result = theme_remove_demo_content($slug);
        
        if ($result['success']) {
            Session::flash('success', "Removed {$result['pages_removed']} demo pages.");
        } else {
            Session::flash('error', $result['message'] ?? 'Failed to remove demo content.');
        }
        Response::redirect('/admin/themes');
    }
    
    private function isAjax(): bool
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        $xhrHeader = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return stripos($contentType, 'application/json') !== false
            || strtolower($xhrHeader) === 'xmlhttprequest';
    }

    private function setActiveTheme(string $slug): bool
    {
        return \SettingsModel::setActiveTheme($slug);
    }

    private function listThemes(): array
    {
        $themes = [];
        if (!is_dir($this->themesDir)) return $themes;
        $dirs = @scandir($this->themesDir);
        if (!$dirs) return $themes;

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || $dir === 'README.md' || $dir === 'core' || $dir === 'presets') continue;
            $themePath = $this->themesDir . '/' . $dir;
            if (!is_dir($themePath)) continue;

            $metadata = [];
            $jsonPath = $themePath . '/theme.json';
            if (file_exists($jsonPath)) {
                $json = @file_get_contents($jsonPath);
                if ($json) $metadata = json_decode($json, true) ?: [];
            }

            $screenshot = null;
            foreach (['screenshot.png', 'screenshot.jpg', 'preview.png', 'preview.jpg'] as $img) {
                if (file_exists($themePath . '/' . $img)) {
                    $screenshot = '/themes/' . $dir . '/' . $img;
                    break;
                }
            }

            $themes[] = [
                'slug' => $dir,
                'name' => $metadata['name'] ?? ucfirst($dir),
                'description' => $metadata['description'] ?? '',
                'version' => $metadata['version'] ?? '1.0.0',
                'author' => $metadata['author'] ?? '',
                'screenshot' => $screenshot,
            ];
        }

        usort($themes, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        return $themes;
    }

    public function customize(Request $request): void
    {
        $slug = $request->param('slug', '');
        if (empty($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }

        $themePath = $this->themesDir . '/' . $slug;
        if (!is_dir($themePath)) {
            Session::flash('error', 'Theme directory not found.');
            Response::redirect('/admin/themes');
            return;
        }

        $themeConfig = [];
        $jsonPath = $themePath . '/theme.json';
        if (file_exists($jsonPath)) {
            $json = @file_get_contents($jsonPath);
            if ($json) $themeConfig = json_decode($json, true) ?: [];
        }

        $currentOptions = [];
        $optionsPath = $themePath . '/options.json';
        if (file_exists($optionsPath)) {
            $json = @file_get_contents($optionsPath);
            if ($json) $currentOptions = json_decode($json, true) ?: [];
        }

        $defaultOptions = $themeConfig['options'] ?? [];
        $options = array_merge($defaultOptions, $currentOptions);
        $supports = $themeConfig['supports'] ?? [];
        $hasCustomize = !empty($supports['blank-canvas']) || !empty($supports['custom-header']) || !empty($supports['custom-footer']) || !empty($defaultOptions);

        render('admin/themes/customize', [
            'theme' => ['slug' => $slug, 'name' => $themeConfig['name'] ?? ucfirst($slug), 'description' => $themeConfig['description'] ?? '', 'version' => $themeConfig['version'] ?? '1.0.0'],
            'themeConfig' => $themeConfig,
            'options' => $options,
            'defaultOptions' => $defaultOptions,
            'supports' => $supports,
            'hasCustomize' => $hasCustomize,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function saveCustomize(Request $request): void
    {
        $slug = $request->param('slug', '');
        if (empty($slug) || !preg_match('/^[A-Za-z0-9_-]+$/', $slug)) {
            Session::flash('error', 'Invalid theme name.');
            Response::redirect('/admin/themes');
            return;
        }

        $themePath = $this->themesDir . '/' . $slug;
        if (!is_dir($themePath)) {
            Session::flash('error', 'Theme directory not found.');
            Response::redirect('/admin/themes');
            return;
        }

        $options = [];
        foreach (['show_header', 'show_footer', 'preload_fonts'] as $field) {
            $options[$field] = $request->post($field) === '1' || $request->post($field) === 'on';
        }
        foreach (['body_background', 'header_style', 'footer_style'] as $field) {
            $value = $request->post($field, '');
            if (!empty($value)) $options[$field] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }

        $optionsPath = $themePath . '/options.json';
        $json = json_encode($options, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if (@file_put_contents($optionsPath, $json) !== false) {
            \Cache::clear('theme_config');
            \Cache::clear('theme_options_' . $slug);
            Session::flash('success', 'Theme options saved successfully!');
        } else {
            Session::flash('error', 'Failed to save theme options. Check file permissions.');
        }

        Response::redirect('/admin/themes/' . $slug . '/customize');
    }
}
