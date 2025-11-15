<?php
class PluginApiController {
    protected $registry;
    protected $installer;

    public function __construct() {
        $this->registry = new EnhancedPluginRegistry();
        $this->installer = new PluginInstaller($this->registry);
    }

    public function listPlugins() {
        try {
            $remotePlugins = $this->registry->fetchRemoteRegistry();
            $installedPlugins = $this->registry->getLicenseCache();
            
            $response = [
                'remote' => $remotePlugins,
                'installed' => $installedPlugins,
                'status' => 'success'
            ];
            
            return json_encode($response);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function installPlugin($pluginId, $licenseKey = null) {
        try {
            $plugin = $this->registry->getPluginDetails($pluginId);
            if (!$plugin) {
                throw new \RuntimeException("Plugin not found");
            }

            $result = $this->installer->installFromUrl(
                $plugin['download_url'],
                $licenseKey
            );

            return json_encode([
                'status' => 'success',
                'plugin_id' => $pluginId,
                'installed' => $result
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function validateLicense($pluginId, $licenseKey) {
        try {
            $valid = $this->registry->validateLicense($pluginId, $licenseKey);
            return json_encode([
                'status' => 'success',
                'valid' => $valid,
                'plugin_id' => $pluginId
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function pluginStatus($pluginId) {
        try {
            $details = $this->registry->getPluginDetails($pluginId);
            if (!$details) {
                throw new \RuntimeException("Plugin not installed");
            }

            return json_encode([
                'status' => 'success',
                'plugin' => $details
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    protected function errorResponse($message) {
        return json_encode([
            'status' => 'error',
            'message' => $message
        ]);
    }
}
