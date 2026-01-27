<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/thememanager.php';

class HomeController
{
    public function index(): void
    {
        $activeTheme = null;

        // Support theme preview via ?theme= OR ?preview_theme= parameter
        $previewParam = $_GET['theme'] ?? $_GET['preview_theme'] ?? '';
        if (!empty($previewParam)) {
            $previewTheme = preg_replace('/[^a-z0-9_-]/', '', strtolower($previewParam));
            $themePath = __DIR__ . '/../themes/' . $previewTheme;
            if ($previewTheme && is_dir($themePath) && file_exists($themePath . '/layout.php')) {
                $activeTheme = $previewTheme;
            }
        }

        // If no preview, get active theme from database via ThemeManager
        if (!$activeTheme) {
            $activeTheme = ThemeManager::getActiveTheme(1, ThemeManager::CONTEXT_PUBLIC);
        }

        // Final fallback to 'default'
        if (!$activeTheme) {
            $activeTheme = 'default';
        }

        echo ThemeManager::render_theme_view_public('home', [
            'title' => 'Jessie AI-CMS',
            'description' => 'The intelligent content management system powered by AI'
        ], $activeTheme);
    }
}
