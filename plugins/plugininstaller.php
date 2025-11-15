<?php
class PluginInstaller {
    protected $registry;
    protected $installDir = __DIR__.'/installed/';
    protected $tempDir = __DIR__.'/temp/';

    public function __construct($registry = null) {
        $this->registry = $registry ?? new EnhancedPluginRegistry();
        $this->ensureDirectories();
    }

    protected function ensureDirectories() {
        if (!is_dir($this->installDir)) {
            mkdir($this->installDir, 0755, true);
        }
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
    }

    public function installFromUrl($url, $licenseKey = null) {
        $tempFile = $this->downloadPlugin($url);
        $pluginData = $this->validatePackage($tempFile);
        
        if ($pluginData['monetization']['type'] !== 'free' && !$licenseKey) {
            throw new \RuntimeException('License key required');
        }

        if ($licenseKey && !$this->registry->validateLicense($pluginData['id'], $licenseKey)) {
            throw new \RuntimeException('Invalid license key');
        }

        $this->unpackPlugin($tempFile, $pluginData['id']);
        $this->registerPlugin($pluginData);
        return true;
    }

    protected function downloadPlugin($url) {
        $tempFile = $this->tempDir . basename($url);
        $data = file_get_contents($url);
        
        if ($data === false) {
            throw new \RuntimeException("Failed to download plugin from $url");
        }

        file_put_contents($tempFile, $data);
        return $tempFile;
    }

    protected function validatePackage($file) {
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException('Invalid plugin package');
        }

        $manifest = $zip->getFromName('plugin.json');
        if (!$manifest) {
            throw new \RuntimeException('Missing plugin manifest');
        }

        $data = json_decode($manifest, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid manifest format');
        }

        $zip->close();
        return $data;
    }

    protected function unpackPlugin($file, $pluginId) {
        $targetDir = $this->installDir . $pluginId . '/';
        
        $zip = new \ZipArchive();
        if ($zip->open($file) !== true) {
            throw new \RuntimeException('Failed to open plugin package');
        }

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip->extractTo($targetDir);
        $zip->close();
        unlink($file);
    }

    protected function registerPlugin($data) {
        // TODO: Implement registration in database
    }
}
