<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ExtensionsController
{
    private string $extensionsDir;

    public function __construct()
    {
        $this->extensionsDir = \CMS_ROOT . '/extensions';
    }

    public function index(Request $request): void
    {
        $pdo = db();
        $stmt = $pdo->query("SELECT * FROM extensions ORDER BY name ASC");
        $extensions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Check which directories still exist
        foreach ($extensions as &$ext) {
            $ext['dir_exists'] = is_dir($this->extensionsDir . '/' . $ext['directory']);
        }

        // Scan for uninstalled extensions
        $available = $this->scanAvailableExtensions($extensions);

        render('admin/extensions/index', [
            'extensions' => $extensions,
            'available' => $available,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function install(Request $request): void
    {
        $directory = basename(trim($request->post('directory', '')));

        if (empty($directory)) {
            Session::flash('error', 'Invalid extension directory.');
            Response::redirect('/admin/extensions');
            return;
        }

        $extPath = $this->extensionsDir . '/' . $directory;
        $manifestPath = $extPath . '/extension.json';

        if (!is_dir($extPath)) {
            Session::flash('error', 'Extension directory not found.');
            Response::redirect('/admin/extensions');
            return;
        }

        // Read manifest
        $manifest = $this->readManifest($manifestPath);
        if (!$manifest) {
            Session::flash('error', 'Invalid or missing extension.json manifest.');
            Response::redirect('/admin/extensions');
            return;
        }

        $pdo = db();

        // Check if already installed
        $stmt = $pdo->prepare("SELECT id FROM extensions WHERE directory = ?");
        $stmt->execute([$directory]);
        if ($stmt->fetch()) {
            Session::flash('error', 'Extension is already installed.');
            Response::redirect('/admin/extensions');
            return;
        }

        // Install
        $stmt = $pdo->prepare("INSERT INTO extensions (name, slug, description, version, author, author_url, directory, is_active, installed_at) VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        $stmt->execute([
            $manifest['name'] ?? $directory,
            $manifest['slug'] ?? $this->generateSlug($manifest['name'] ?? $directory),
            $manifest['description'] ?? '',
            $manifest['version'] ?? '1.0.0',
            $manifest['author'] ?? '',
            $manifest['author_url'] ?? '',
            $directory
        ]);

        // Run install hook if exists
        $this->runHook($extPath, 'install');

        Session::flash('success', 'Extension installed successfully.');
        Response::redirect('/admin/extensions');
    }

    public function toggle(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM extensions WHERE id = ?");
        $stmt->execute([$id]);
        $ext = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ext) {
            Session::flash('error', 'Extension not found.');
            Response::redirect('/admin/extensions');
            return;
        }

        $newStatus = $ext['is_active'] ? 0 : 1;
        $extPath = $this->extensionsDir . '/' . $ext['directory'];

        // Run activate/deactivate hook
        if ($newStatus) {
            $this->runHook($extPath, 'activate');
        } else {
            $this->runHook($extPath, 'deactivate');
        }

        $stmt = $pdo->prepare("UPDATE extensions SET is_active = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);

        Session::flash('success', 'Extension ' . ($newStatus ? 'activated' : 'deactivated') . '.');
        Response::redirect('/admin/extensions');
    }

    public function uninstall(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM extensions WHERE id = ?");
        $stmt->execute([$id]);
        $ext = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ext) {
            Session::flash('error', 'Extension not found.');
            Response::redirect('/admin/extensions');
            return;
        }

        $extPath = $this->extensionsDir . '/' . $ext['directory'];

        // Run uninstall hook
        $this->runHook($extPath, 'uninstall');

        // Remove from database (keep files for manual deletion via FTP)
        $stmt = $pdo->prepare("DELETE FROM extensions WHERE id = ?");
        $stmt->execute([$id]);

        Session::flash('success', 'Extension uninstalled. Files can be removed via FTP.');
        Response::redirect('/admin/extensions');
    }

    public function settings(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM extensions WHERE id = ?");
        $stmt->execute([$id]);
        $ext = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ext) {
            Session::flash('error', 'Extension not found.');
            Response::redirect('/admin/extensions');
            return;
        }

        $extPath = $this->extensionsDir . '/' . $ext['directory'];
        $settingsFile = $extPath . '/settings.php';

        $settingsFields = [];
        if (file_exists($settingsFile)) {
            $settingsFields = include $settingsFile;
        }

        $currentSettings = json_decode($ext['settings'] ?? '{}', true) ?: [];

        render('admin/extensions/settings', [
            'extension' => $ext,
            'settingsFields' => $settingsFields,
            'currentSettings' => $currentSettings,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function saveSettings(Request $request): void
    {
        $id = (int)$request->param('id');

        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM extensions WHERE id = ?");
        $stmt->execute([$id]);
        $ext = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$ext) {
            Session::flash('error', 'Extension not found.');
            Response::redirect('/admin/extensions');
            return;
        }

        // Get all posted settings (exclude csrf_token)
        $settings = $_POST;
        unset($settings['csrf_token']);

        $stmt = $pdo->prepare("UPDATE extensions SET settings = ? WHERE id = ?");
        $stmt->execute([json_encode($settings), $id]);

        Session::flash('success', 'Settings saved.');
        Response::redirect("/admin/extensions/{$id}/settings");
    }

    private function scanAvailableExtensions(array $installed): array
    {
        $available = [];
        $installedDirs = array_column($installed, 'directory');

        if (!is_dir($this->extensionsDir)) {
            return [];
        }

        $dirs = scandir($this->extensionsDir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..' || in_array($dir, $installedDirs)) {
                continue;
            }

            $extPath = $this->extensionsDir . '/' . $dir;
            if (!is_dir($extPath)) {
                continue;
            }

            $manifest = $this->readManifest($extPath . '/extension.json');
            if ($manifest) {
                $available[] = [
                    'directory' => $dir,
                    'name' => $manifest['name'] ?? $dir,
                    'description' => $manifest['description'] ?? '',
                    'version' => $manifest['version'] ?? '1.0.0',
                    'author' => $manifest['author'] ?? ''
                ];
            }
        }

        return $available;
    }

    private function readManifest(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        return is_array($data) ? $data : null;
    }

    private function runHook(string $extPath, string $hook): void
    {
        $hookFile = $extPath . '/hooks/' . $hook . '.php';
        if (file_exists($hookFile)) {
            include $hookFile;
        }
    }

    private function generateSlug(string $name): string
    {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }
}
