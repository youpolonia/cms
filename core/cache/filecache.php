<?php
namespace Core\Cache;

class FileCache implements CacheInterface {
    private string $cacheDir;
    private string $fileExtension = '.cache';

    public function __construct(string $cacheDir = null) {
        if ($cacheDir === null) {
            require_once __DIR__ . '/../tmp_sandbox.php';
            $cacheDir = cms_tmp_path('cms_cache');
        }
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function set(string $tenantId, string $key, mixed $value, ?int $ttl = null): bool {
        $filename = $this->getFilename($tenantId, $key);
        $data = [
            'value' => $value,
            'expires' => $ttl ? time() + $ttl : null
        ];

        $tempFile = tempnam($this->cacheDir, 'tmp_');
        if (file_put_contents($tempFile, serialize($data)) === false) {
            return false;
        }
        
        return rename($tempFile, $filename);
    }

    public function get(string $tenantId, string $key) {
        $filename = $this->getFilename($tenantId, $key);
        
        if (!file_exists($filename)) {
            return null;
        }
        
        // Enforce file size limit (1MB)
        if (filesize($filename) > 1048576) {
            error_log('Cache file exceeds size limit: ' . $filename);
            $this->delete($tenantId, $key);
            return null;
        }
        
        $data = unserialize(file_get_contents($filename), ['allowed_classes' => false]);
        if ($data === false || !is_array($data)) {
            error_log('Invalid cache data format');
            $this->delete($tenantId, $key);
            return null;
        }
        
        if ($data['expires'] && time() > $data['expires']) {
            $this->delete($tenantId, $key);
            return null;
        }
        
        return $data['value'];
    }

    public function delete(string $tenantId, string $key): bool {
        $filename = $this->getFilename($tenantId, $key);
        if (file_exists($filename)) {
            return unlink($filename);
        }
        return true;
    }

    public function clear(string $tenantId): bool {
        $files = glob($this->cacheDir . $tenantId . '_*' . $this->fileExtension);
        $success = true;
        
        foreach ($files as $file) {
            if (!unlink($file)) {
                $success = false;
            }
        }
        
        return $success;
    }

    public function has(string $tenantId, string $key): bool {
        $filename = $this->getFilename($tenantId, $key);
        
        if (!file_exists($filename)) {
            return false;
        }
        
        // Enforce file size limit (1MB)
        if (filesize($filename) > 1048576) {
            error_log('Cache file exceeds size limit: ' . $filename);
            $this->delete($tenantId, $key);
            return false;
        }
        
        $data = unserialize(file_get_contents($filename), ['allowed_classes' => false]);
        if ($data === false || !is_array($data)) {
            error_log('Invalid cache data format');
            $this->delete($tenantId, $key);
            return false;
        }
        
        if ($data['expires'] && time() > $data['expires']) {
            $this->delete($tenantId, $key);
            return false;
        }
        
        return true;
    }

    private function getFilename(string $tenantId, string $key): string {
        $safeTenant = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $tenantId);
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->cacheDir . $safeTenant . '_' . $safeKey . $this->fileExtension;
    }
}
