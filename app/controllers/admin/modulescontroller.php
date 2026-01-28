<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class ModulesController
{
    private string $modulesDir;
    private string $stateFile;
    private array $coreModules = ['admin', 'auth', 'content'];

    public function __construct()
    {
        $this->modulesDir = \CMS_ROOT . '/modules';
        $this->stateFile = \CMS_ROOT . '/cms_storage/modules_state.json';
        $this->ensureStorageDir();
    }

    public function index(Request $request): void
    {
        $modules = $this->scanModules();
        $state = $this->loadState();
        $stats = $this->calculateStats($modules, $state);

        $filter = $request->get('filter', 'all');
        $search = $request->get('search', '');

        $filteredModules = $this->filterModules($modules, $state, $filter, $search);

        render('admin/modules/index', [
            'modules' => $filteredModules,
            'state' => $state,
            'stats' => $stats,
            'filter' => $filter,
            'search' => $search,
            'coreModules' => $this->coreModules,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function show(Request $request): void
    {
        $slug = basename(trim($request->param('slug', '')));

        if (empty($slug)) {
            Session::flash('error', 'Invalid module.');
            Response::redirect('/admin/modules');
            return;
        }

        $modulePath = $this->modulesDir . '/' . $slug;

        if (!is_dir($modulePath)) {
            Session::flash('error', 'Module not found.');
            Response::redirect('/admin/modules');
            return;
        }

        $module = $this->getModuleInfo($slug, $modulePath);
        $state = $this->loadState();
        $module['is_enabled'] = $state[$slug]['enabled'] ?? true;
        $module['is_core'] = in_array($slug, $this->coreModules);

        $files = $this->getModuleFiles($modulePath);
        $dependencies = $this->analyzeDependencies($module);

        render('admin/modules/show', [
            'module' => $module,
            'files' => $files,
            'dependencies' => $dependencies,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function toggle(Request $request): void
    {
        csrf_validate_or_403();

        $slug = basename(trim($request->param('slug', '')));

        if (empty($slug)) {
            Session::flash('error', 'Invalid module.');
            Response::redirect('/admin/modules');
            return;
        }

        if (in_array($slug, $this->coreModules)) {
            Session::flash('error', 'Core modules cannot be disabled.');
            Response::redirect('/admin/modules');
            return;
        }

        $modulePath = $this->modulesDir . '/' . $slug;

        if (!is_dir($modulePath)) {
            Session::flash('error', 'Module not found.');
            Response::redirect('/admin/modules');
            return;
        }

        $state = $this->loadState();
        $currentlyEnabled = $state[$slug]['enabled'] ?? true;
        $newState = !$currentlyEnabled;

        $state[$slug] = [
            'enabled' => $newState,
            'toggled_at' => date('Y-m-d H:i:s'),
            'toggled_by' => Session::getAdminUsername() ?? 'admin'
        ];

        if ($this->saveState($state)) {
            $action = $newState ? 'enabled' : 'disabled';
            $this->logModuleAction($slug, $action);
            Session::flash('success', "Module '{$slug}' has been {$action}.");
        } else {
            Session::flash('error', 'Failed to update module state. Check file permissions.');
        }

        Response::redirect('/admin/modules');
    }

    public function refresh(Request $request): void
    {
        csrf_validate_or_403();

        $modules = $this->scanModules();
        $state = $this->loadState();

        $updated = 0;
        foreach ($modules as $module) {
            if (!isset($state[$module['slug']])) {
                $state[$module['slug']] = [
                    'enabled' => true,
                    'discovered_at' => date('Y-m-d H:i:s')
                ];
                $updated++;
            }
        }

        $removed = 0;
        $existingSlugs = array_column($modules, 'slug');
        foreach (array_keys($state) as $slug) {
            if (!in_array($slug, $existingSlugs)) {
                unset($state[$slug]);
                $removed++;
            }
        }

        $this->saveState($state);

        $message = "Module cache refreshed. Found {$updated} new module(s).";
        if ($removed > 0) {
            $message .= " Removed {$removed} orphaned record(s).";
        }

        Session::flash('success', $message);
        Response::redirect('/admin/modules');
    }

    public function bulkAction(Request $request): void
    {
        csrf_validate_or_403();

        $action = $request->post('action', '');
        $selected = $request->post('modules', []);

        if (empty($selected) || !is_array($selected)) {
            Session::flash('error', 'No modules selected.');
            Response::redirect('/admin/modules');
            return;
        }

        $state = $this->loadState();
        $processed = 0;

        foreach ($selected as $slug) {
            $slug = basename($slug);

            if (in_array($slug, $this->coreModules)) {
                continue;
            }

            if (!is_dir($this->modulesDir . '/' . $slug)) {
                continue;
            }

            switch ($action) {
                case 'enable':
                    $state[$slug] = [
                        'enabled' => true,
                        'toggled_at' => date('Y-m-d H:i:s'),
                        'toggled_by' => Session::getAdminUsername() ?? 'admin'
                    ];
                    $processed++;
                    break;

                case 'disable':
                    $state[$slug] = [
                        'enabled' => false,
                        'toggled_at' => date('Y-m-d H:i:s'),
                        'toggled_by' => Session::getAdminUsername() ?? 'admin'
                    ];
                    $processed++;
                    break;
            }
        }

        if ($processed > 0) {
            $this->saveState($state);
            $actionText = $action === 'enable' ? 'enabled' : 'disabled';
            Session::flash('success', "{$processed} module(s) {$actionText}.");
        } else {
            Session::flash('error', 'No modules were processed. Core modules cannot be modified.');
        }

        Response::redirect('/admin/modules');
    }

    private function scanModules(): array
    {
        $modules = [];

        if (!is_dir($this->modulesDir)) {
            return [];
        }

        $dirs = scandir($this->modulesDir);
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $modulePath = $this->modulesDir . '/' . $dir;
            if (!is_dir($modulePath)) {
                continue;
            }

            $modules[] = $this->getModuleInfo($dir, $modulePath);
        }

        usort($modules, function($a, $b) {
            $aCore = in_array($a['slug'], $this->coreModules);
            $bCore = in_array($b['slug'], $this->coreModules);
            if ($aCore !== $bCore) {
                return $bCore - $aCore;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $modules;
    }

    private function getModuleInfo(string $slug, string $path): array
    {
        $manifest = $this->readManifest($path . '/manifest.json');

        $info = [
            'slug' => $slug,
            'path' => $path,
            'name' => $manifest['name'] ?? $this->formatName($slug),
            'version' => $manifest['version'] ?? '1.0.0',
            'description' => $manifest['description'] ?? '',
            'author' => $manifest['author'] ?? '',
            'license' => $manifest['license'] ?? '',
            'dependencies' => $manifest['dependencies'] ?? [],
            'routes' => $manifest['routes'] ?? [],
            'hooks' => $manifest['hooks'] ?? [],
            'autoload' => $manifest['autoload'] ?? [],
            'has_manifest' => $manifest !== null,
            'file_count' => $this->countFiles($path),
            'size' => $this->getDirectorySize($path),
            'modified_at' => date('Y-m-d H:i:s', filemtime($path))
        ];

        return $info;
    }

    private function getModuleFiles(string $path): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($path . '/', '', $file->getPathname());
                $files[] = [
                    'path' => $relativePath,
                    'size' => $file->getSize(),
                    'type' => $file->getExtension(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime())
                ];
            }
        }

        usort($files, function($a, $b) {
            return strcasecmp($a['path'], $b['path']);
        });

        return $files;
    }

    private function analyzeDependencies(array $module): array
    {
        $deps = [
            'required' => [],
            'optional' => [],
            'missing' => []
        ];

        if (!empty($module['dependencies'])) {
            $state = $this->loadState();

            foreach ($module['dependencies'] as $dep => $version) {
                $depPath = $this->modulesDir . '/' . $dep;

                if (is_dir($depPath)) {
                    $isEnabled = $state[$dep]['enabled'] ?? true;
                    $deps['required'][] = [
                        'name' => $dep,
                        'version' => $version,
                        'installed' => true,
                        'enabled' => $isEnabled
                    ];
                } else {
                    $deps['missing'][] = [
                        'name' => $dep,
                        'version' => $version
                    ];
                }
            }
        }

        return $deps;
    }

    private function filterModules(array $modules, array $state, string $filter, string $search): array
    {
        return array_filter($modules, function($module) use ($state, $filter, $search) {
            $isEnabled = $state[$module['slug']]['enabled'] ?? true;
            $isCore = in_array($module['slug'], $this->coreModules);

            switch ($filter) {
                case 'enabled':
                    if (!$isEnabled && !$isCore) return false;
                    break;
                case 'disabled':
                    if ($isEnabled || $isCore) return false;
                    break;
                case 'core':
                    if (!$isCore) return false;
                    break;
                case 'third-party':
                    if ($isCore) return false;
                    break;
            }

            if (!empty($search)) {
                $searchLower = strtolower($search);
                $matchName = stripos($module['name'], $search) !== false;
                $matchSlug = stripos($module['slug'], $search) !== false;
                $matchDesc = stripos($module['description'], $search) !== false;

                if (!$matchName && !$matchSlug && !$matchDesc) {
                    return false;
                }
            }

            return true;
        });
    }

    private function calculateStats(array $modules, array $state): array
    {
        $total = count($modules);
        $enabled = 0;
        $disabled = 0;
        $core = 0;
        $withManifest = 0;

        foreach ($modules as $module) {
            $isEnabled = $state[$module['slug']]['enabled'] ?? true;
            $isCore = in_array($module['slug'], $this->coreModules);

            if ($isCore || $isEnabled) {
                $enabled++;
            } else {
                $disabled++;
            }

            if ($isCore) {
                $core++;
            }

            if ($module['has_manifest']) {
                $withManifest++;
            }
        }

        return [
            'total' => $total,
            'enabled' => $enabled,
            'disabled' => $disabled,
            'core' => $core,
            'third_party' => $total - $core,
            'with_manifest' => $withManifest
        ];
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

    private function loadState(): array
    {
        if (!file_exists($this->stateFile)) {
            return [];
        }

        $content = file_get_contents($this->stateFile);
        $data = json_decode($content, true);

        return is_array($data) ? $data : [];
    }

    private function saveState(array $state): bool
    {
        $json = json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        return file_put_contents($this->stateFile, $json) !== false;
    }

    private function ensureStorageDir(): void
    {
        $storageDir = dirname($this->stateFile);
        if (!is_dir($storageDir)) {
            @mkdir($storageDir, 0755, true);
        }
    }

    private function formatName(string $slug): string
    {
        $name = str_replace(['-', '_'], ' ', $slug);
        return ucwords($name);
    }

    private function countFiles(string $path): int
    {
        $count = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $count++;
            }
        }

        return $count;
    }

    private function getDirectorySize(string $path): int
    {
        $size = 0;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    private function logModuleAction(string $slug, string $action): void
    {
        $logFile = \CMS_ROOT . '/logs/modules.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $entry = json_encode([
            'timestamp' => date('c'),
            'module' => $slug,
            'action' => $action,
            'user' => Session::getAdminUsername() ?? 'admin',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]) . "\n";

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }
}
