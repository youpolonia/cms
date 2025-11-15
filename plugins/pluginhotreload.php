<?php
// plugins/PluginHotReload.php
class PluginHotReload {
    const MAX_VERSIONS = 5;
    
    public static function triggerReload(): array {
        $dependencyGraph = PluginRegistry::getDependencyGraph();
        $validationResult = self::validateDependencies($dependencyGraph);
        
        if ($validationResult['valid']) {
            $snapshotPath = self::createSnapshot();
            PluginRegistry::updateFromCache();
            return ['status' => 'success', 'snapshot' => $snapshotPath];
        }
        
        return ['status' => 'error', 'issues' => $validationResult['conflicts']];
    }
    
    private static function validateDependencies(array $dependencies): array {
        $validator = new DependencyValidator();
        return $validator->checkConsistency($dependencies);
    }
    
    private static function createSnapshot(): string {
        $version = date('Ymd-His');
        $backupDir = __DIR__ . '/backups/' . $version;
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $plugins = PluginRegistry::getAllPlugins();
        foreach ($plugins as $plugin) {
            $plugin->saveSnapshot($backupDir);
        }
        
        self::cleanupOldBackups();
        return $backupDir;
    }
    
    private static function cleanupOldBackups(): void {
        $backupDir = __DIR__ . '/backups/';
        $versions = glob($backupDir . '*', GLOB_ONLYDIR);
        
        if (count($versions) > self::MAX_VERSIONS) {
            usort($versions, function($a, $b) {
                return filemtime($a) <=> filemtime($b);
            });
            
            for ($i = 0; $i < count($versions) - self::MAX_VERSIONS; $i++) {
                self::deleteBackup($versions[$i]);
            }
        }
    }
    
    private static function deleteBackup(string $path): void {
        // Implementation for recursive directory deletion
    }
}
