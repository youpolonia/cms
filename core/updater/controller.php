<?php
namespace Core\Updater;

use Core\Updater\Exceptions\UpdateException;
use Core\Updater\Exceptions\ValidationException;

class Controller
{
    private $packageHandler;
    private $backupManager;

    public function __construct()
    {
        $this->packageHandler = new PackageHandler();
        $this->backupManager = new BackupManager();
    }

    /**
     * Checks remote update index for available updates
     * @param string $indexUrl URL to JSON update index
     * @return array Array of available updates
     * @throws UpdateException
     */
    public function checkForUpdates(string $indexUrl): array
    {
        try {
            $indexContent = file_get_contents($indexUrl);
            if ($indexContent === false) {
                throw new UpdateException("Failed to fetch update index");
            }

            $indexData = json_decode($indexContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new UpdateException("Invalid update index format");
            }

            return $this->filterAvailableUpdates($indexData);
        } catch (\Exception $e) {
            throw new UpdateException("Update check failed: " . $e->getMessage());
        }
    }

    /**
     * Downloads and applies an update package
     * @param string $packageUrl URL to update package
     * @param string $checksum Expected SHA-256 checksum
     * @return bool True on success
     * @throws UpdateException|ValidationException
     */
    public function applyUpdate(string $packageUrl, string $checksum): bool
    {
        try {
            // Download and validate package
            $packagePath = $this->packageHandler->download($packageUrl, $checksum);
            
            // Create backup before applying update
            $backupId = $this->backupManager->createBackup([
                '/core/',
                '/plugins/',
                '/templates/'
            ]);

            // Extract and apply update
            $extractedFiles = $this->packageHandler->extract($packagePath, ROOT_PATH);
            
            // Clean up temporary files
            unlink($packagePath);

            return true;
        } catch (\Exception $e) {
            if (isset($backupId)) {
                $this->backupManager->restoreBackup($backupId);
            }
            throw $e;
        }
    }

    private function filterAvailableUpdates(array $indexData): array
    {
        $available = [];
        
        // Core updates
        if (isset($indexData['core'])) {
            $currentVersion = $this->getCurrentCoreVersion();
            if (version_compare($indexData['core']['version'], $currentVersion, '>')) {
                $available['core'] = $indexData['core'];
            }
        }

        // Plugin updates
        if (isset($indexData['plugins'])) {
            foreach ($indexData['plugins'] as $pluginId => $pluginData) {
                if ($this->isPluginUpdateAvailable($pluginId, $pluginData['version'])) {
                    $available['plugins'][$pluginId] = $pluginData;
                }
            }
        }

        return $available;
    }

    private function getCurrentCoreVersion(): string
    {
        // Implementation depends on your version tracking system
        return '1.0.0'; // Placeholder
    }

    private function isPluginUpdateAvailable(string $pluginId, string $newVersion): bool
    {
        // Implementation depends on your plugin system
        return false; // Placeholder
    }
}
