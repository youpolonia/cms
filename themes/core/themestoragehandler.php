<?php
require_once __DIR__ . '/../../models/versionmodel.php';

class ThemeStorageHandler {
    private static $storagePath = __DIR__ . '/../../themes/';

    public static function saveThemeVersion($themeId, $config, $createdBy, $notes) {
        $versionPath = self::$storagePath . "{$themeId}/versions/";
        if (!file_exists($versionPath)) {
            mkdir($versionPath, 0755, true);
        }

        // Get next version number
        $versions = glob($versionPath . 'version_*.json');
        $nextVersion = count($versions) + 1;

        $versionData = [
            'config' => $config,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $createdBy,
            'notes' => $notes
        ];

        $filePath = $versionPath . "version_{$nextVersion}.json";
        return file_put_contents($filePath, json_encode($versionData, JSON_PRETTY_PRINT));
    }

    public static function getThemeVersions($themeId) {
        $versionPath = self::$storagePath . "{$themeId}/versions/";
        if (!file_exists($versionPath)) {
            return [];
        }

        $versions = [];
        foreach (glob($versionPath . 'version_*.json') as $file) {
            $versionNum = (int) str_replace(['version_', '.json'], '', basename($file));
            $versions[$versionNum] = json_decode(file_get_contents($file), true);
        }

        krsort($versions);
        return $versions;
    }

    public static function restoreThemeVersion($themeId, $versionNum) {
        $filePath = self::$storagePath . "{$themeId}/versions/version_{$versionNum}.json";
        if (!file_exists($filePath)) {
            return false;
        }

        $versionData = json_decode(file_get_contents($filePath), true);
        $themeConfigPath = self::$storagePath . "{$themeId}/theme.json";

        return file_put_contents($themeConfigPath, json_encode($versionData['config'], JSON_PRETTY_PRINT));
    }
}
