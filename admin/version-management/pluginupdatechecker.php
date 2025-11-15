<?php
class PluginUpdateChecker {
    private $manualOverrides = [];

    public function checkForUpdates($pluginName, $currentVersion, $latestVersion) {
        // Check if there's a manual override
        if (isset($this->manualOverrides[$pluginName])) {
            return $this->manualOverrides[$pluginName];
        }

        return version_compare($currentVersion, $latestVersion, '<');
    }

    public function getLatestVersion($pluginName) {
        // In a real implementation, this would fetch from repository
        // For now returns a mock version
        return '1.2.0';
    }

    public function setManualOverride($pluginName, $status) {
        $this->manualOverrides[$pluginName] = (bool)$status;
    }

    public function clearManualOverride($pluginName) {
        unset($this->manualOverrides[$pluginName]);
    }

    public function getUpdateNotification($pluginName, $currentVersion) {
        $latestVersion = $this->getLatestVersion($pluginName);
        if ($this->checkForUpdates($pluginName, $currentVersion, $latestVersion)) {
            return [
                'plugin' => $pluginName,
                'current_version' => $currentVersion,
                'latest_version' => $latestVersion,
                'has_update' => true
            ];
        }
        return null;
    }
}
