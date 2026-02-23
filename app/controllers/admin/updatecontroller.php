<?php
declare(strict_types=1);

namespace Admin;

use Core\Request;
use Core\Session;

class UpdateController
{
    private const VERSION_FILE = CMS_ROOT . '/version.json';
    private const REMOTE_VERSION_URL = 'https://raw.githubusercontent.com/youpolonia/cms/main/version.json';

    public function index(Request $request): void
    {
        Session::requireRole('admin');

        $currentVersion = $this->getCurrentVersion();
        $latestVersion = $this->getLatestVersion();
        $updateAvailable = $latestVersion && version_compare($latestVersion['version'] ?? '0', $currentVersion['version'] ?? '0', '>');

        render('admin/updates/index', [
            'currentVersion' => $currentVersion,
            'latestVersion' => $latestVersion,
            'updateAvailable' => $updateAvailable,
            'phpVersion' => PHP_VERSION,
            'mysqlVersion' => $this->getMysqlVersion(),
            'diskFree' => disk_free_space(CMS_ROOT),
            'diskTotal' => disk_total_space(CMS_ROOT),
            'lastCheck' => $this->getLastCheckTime(),
        ]);
    }

    public function check(Request $request): void
    {
        Session::requireRole('admin');

        $latestVersion = $this->getLatestVersion(true); // Force refresh
        $currentVersion = $this->getCurrentVersion();
        $updateAvailable = $latestVersion && version_compare($latestVersion['version'] ?? '0', $currentVersion['version'] ?? '0', '>');

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'current' => $currentVersion['version'] ?? 'unknown',
            'latest' => $latestVersion['version'] ?? 'unknown',
            'updateAvailable' => $updateAvailable,
            'changelog' => $latestVersion['changelog'] ?? '',
        ]);
        exit;
    }

    private function getCurrentVersion(): array
    {
        if (file_exists(self::VERSION_FILE)) {
            $data = json_decode(file_get_contents(self::VERSION_FILE), true);
            if (is_array($data)) return $data;
        }
        return ['version' => '0.9.0', 'released' => '2026-02-23', 'codename' => 'Jessie'];
    }

    private function getLatestVersion(bool $forceRefresh = false): ?array
    {
        $cacheFile = sys_get_temp_dir() . '/jessie_cms_version_check.json';

        // Cache for 1 hour
        if (!$forceRefresh && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached)) return $cached;
        }

        try {
            $ctx = stream_context_create([
                'http' => ['timeout' => 5, 'user_agent' => 'Jessie CMS/' . ($this->getCurrentVersion()['version'] ?? '0')]
            ]);
            $json = @file_get_contents(self::REMOTE_VERSION_URL, false, $ctx);
            if ($json) {
                $data = json_decode($json, true);
                if (is_array($data) && isset($data['version'])) {
                    file_put_contents($cacheFile, $json);
                    return $data;
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — no internet or repo not set up yet
        }

        return null;
    }

    private function getMysqlVersion(): string
    {
        try {
            $pdo = db();
            return $pdo->query("SELECT VERSION()")->fetchColumn() ?: 'unknown';
        } catch (\Throwable $e) {
            return 'unknown';
        }
    }

    private function getLastCheckTime(): ?string
    {
        $cacheFile = sys_get_temp_dir() . '/jessie_cms_version_check.json';
        if (file_exists($cacheFile)) {
            return date('Y-m-d H:i:s', filemtime($cacheFile));
        }
        return null;
    }
}
