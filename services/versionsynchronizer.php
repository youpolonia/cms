<?php
class VersionSynchronizer {
    public static function getUpdates($tenantId, $currentVersion = null) {
        $versions = self::loadVersions($tenantId);
        
        if ($currentVersion === null) {
            return $versions;
        }

        return array_filter($versions, function($v) use ($currentVersion) {
            return version_compare($v['version'], $currentVersion) > 0;
        });
    }

    private static function loadVersions($tenantId) {
        $path = __DIR__ . '/../../storage/versions/' . $tenantId . '.json';
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?: [];
    }

    public static function registerVersion($tenantId, $version, $content) {
        $versions = self::loadVersions($tenantId);
        $versions[] = [
            'version' => $version,
            'content' => $content,
            'timestamp' => time()
        ];
        self::saveVersions($tenantId, $versions);
    }

    private static function saveVersions($tenantId, $versions) {
        $dir = __DIR__ . '/../../storage/versions/';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents(
            $dir . $tenantId . '.json',
            json_encode($versions)
        );
    }
}
