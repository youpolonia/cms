<?php
/**
 * Theme Management Helper Functions
 *
 * Provides simple function-based API for theme management.
 * Wraps existing ThemeManager class and config file storage.
 *
 * @package CMS
 */

if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__));
}

require_once CMS_ROOT . '/core/thememanager.php';

/**
 * Get the currently active theme slug
 *
 * @return string Theme slug (e.g., 'default', 'light')
 */
function theme_get_active(): string
{
    return ThemeManager::getActiveTheme();
}

/**
 * Set the active theme
 *
 * @param string $slug Theme slug to activate
 * @return bool Success status
 */
function theme_set_active(string $slug): bool
{
    require_once CMS_ROOT . '/models/settingsmodel.php';
    return \SettingsModel::setActiveTheme($slug);
}

/**
 * Get list of all available themes
 *
 * @return array Array of theme data with slug, name, description, version, author
 */
function theme_list(): array
{
    $themes = [];
    $themesPath = CMS_ROOT . '/themes';

    if (!is_dir($themesPath)) {
        return $themes;
    }

    $dirs = scandir($themesPath);
    if ($dirs === false) {
        return $themes;
    }

    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..' || $dir === 'README.md') {
            continue;
        }

        $themePath = $themesPath . '/' . $dir;

        if (!is_dir($themePath)) {
            continue;
        }

        // Get metadata from theme.json or theme.php
        $metadata = theme_get_metadata($dir);

        // Skip if no valid metadata
        if (empty($metadata)) {
            continue;
        }

        $themes[] = [
            'slug' => $dir,
            'name' => $metadata['name'] ?? ucfirst($dir),
            'description' => $metadata['description'] ?? '',
            'version' => $metadata['version'] ?? 'Unknown',
            'author' => $metadata['author'] ?? 'Unknown'
        ];
    }

    // Sort by name
    usort($themes, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    return $themes;
}

/**
 * Get theme metadata
 *
 * @param string $slug Theme slug
 * @return array Theme metadata
 */
function theme_get_metadata(string $slug): array
{
    $themePath = CMS_ROOT . '/themes/' . $slug;

    // Try theme.json first
    $jsonPath = $themePath . '/theme.json';
    if (file_exists($jsonPath)) {
        $json = file_get_contents($jsonPath);
        if ($json !== false) {
            $metadata = json_decode($json, true);
            if (is_array($metadata)) {
                return $metadata;
            }
        }
    }

    // Try theme.php
    $phpPath = $themePath . '/theme.php';
    if (file_exists($phpPath)) {
        try {
            $metadata = require $phpPath;
            if (is_array($metadata)) {
                return $metadata;
            }
        } catch (Throwable $e) {
            error_log('theme_get_metadata: Error loading theme.php for ' . $slug . ': ' . $e->getMessage());
        }
    }

    return [];
}

/**
 * Check if a theme exists
 *
 * @param string $slug Theme slug
 * @return bool
 */
function theme_exists(string $slug): bool
{
    $themePath = CMS_ROOT . '/themes/' . $slug;
    return is_dir($themePath);
}
