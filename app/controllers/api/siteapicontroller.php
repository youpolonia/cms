<?php
declare(strict_types=1);

namespace Api;

require_once __DIR__ . '/../../../core/api_middleware.php';

use Core\Request;

/**
 * Site API Controller — public site info endpoint
 */
class SiteApiController
{
    public function __construct()
    {
        \Core\api_rate_limit(60);
    }

    /**
     * GET /api/v1/site
     */
    public function index(?Request $request = null): void
    {
        try {
            $pdo = \core\Database::connection();

            // Get settings — table uses `key`/`value` columns
            $keys = ['site_name', 'site_description', 'site_url', 'active_theme', 'timezone', 'language', 'site_logo', 'site_favicon'];
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings WHERE `key` IN ($placeholders)");
            $stmt->execute($keys);
            $settings = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

            // Also check system_settings for active_theme
            if (empty($settings['active_theme'])) {
                $stmt = $pdo->query("SELECT active_theme FROM system_settings LIMIT 1");
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row) $settings['active_theme'] = $row['active_theme'];
            }

            // Also check system_settings for site_name
            if (empty($settings['site_name'])) {
                $stmt = $pdo->query("SELECT site_name FROM system_settings LIMIT 1");
                $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($row && !empty($row['site_name'])) $settings['site_name'] = $row['site_name'];
            }

            $activeTheme = $settings['active_theme'] ?? 'default';
            $themeInfo = $this->getThemeInfo($activeTheme);

            $version = defined('CMS_VERSION') ? CMS_VERSION : '1.0.0';

            $stats = $this->getSiteStats($pdo);

            \Core\api_json_response([
                'data' => [
                    'name' => $settings['site_name'] ?? 'Jessie CMS',
                    'description' => $settings['site_description'] ?? '',
                    'url' => $settings['site_url'] ?? ($_SERVER['HTTP_HOST'] ?? ''),
                    'version' => $version,
                    'cms' => 'Jessie CMS',
                    'theme' => [
                        'name' => $activeTheme,
                        'version' => $themeInfo['version'] ?? '1.0.0',
                        'description' => $themeInfo['description'] ?? '',
                    ],
                    'language' => $settings['language'] ?? 'en',
                    'stats' => $stats,
                    'api' => [
                        'version' => 'v1',
                        'endpoints' => [
                            'articles' => '/api/v1/articles',
                            'pages' => '/api/v1/pages',
                            'menus' => '/api/v1/menus',
                            'site' => '/api/v1/site',
                        ],
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            error_log("Site API error: " . $e->getMessage());
            \Core\api_error('Failed to fetch site information', 500);
        }
    }

    private function getThemeInfo(string $themeName): array
    {
        $themeDir = dirname(__DIR__, 3) . "/themes/$themeName";
        $jsonFile = "$themeDir/theme.json";
        if (file_exists($jsonFile)) {
            $data = @json_decode(file_get_contents($jsonFile), true);
            if ($data) {
                return [
                    'version' => $data['version'] ?? '1.0.0',
                    'description' => $data['description'] ?? '',
                ];
            }
        }
        return ['version' => '1.0.0', 'description' => ''];
    }

    private function getSiteStats(\PDO $pdo): array
    {
        $stats = [];
        try {
            $stats['articles'] = (int)$pdo->query("SELECT COUNT(*) FROM articles WHERE status = 'published'")->fetchColumn();
            $stats['pages'] = (int)$pdo->query("SELECT COUNT(*) FROM pages WHERE status = 'published'")->fetchColumn();
            $stats['menus'] = (int)$pdo->query("SELECT COUNT(*) FROM menus")->fetchColumn();
        } catch (\Throwable $e) {
            $stats = ['articles' => 0, 'pages' => 0, 'menus' => 0];
        }
        return $stats;
    }
}
