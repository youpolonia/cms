<?php

declare(strict_types=1);

namespace CMS\Cache;

class FileCache implements CacheInterface
{
    private string $cacheDir;
    private string $tenantPrefix;

    public function __construct(string $cacheDir, string $tenantId)
    {
        $this->cacheDir = rtrim($cacheDir, '/') . '/';
        $this->tenantPrefix = 'tenant_' . $tenantId . '_';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return $default;
        }

        // Enforce file size limit (1MB)
        if (filesize($filename) > 1048576) {
            error_log('Cache file exceeds size limit: ' . $filename);
            $this->delete($key);
            return $default;
        }

        $data = unserialize(file_get_contents($filename), ['allowed_classes' => false]);
        if (!is_array($data)) {
            error_log('Invalid cache data format');
            $this->delete($key);
            return $default;
        }
        
        if ($data['expire'] > 0 && $data['expire'] < time()) {
            $this->delete($key);
            return $default;
        }

        return $data['value'];
    }

    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $filename = $this->getFilename($key);
        $data = [
            'value' => $value,
            'expire' => $ttl ? time() + $ttl : 0
        ];

        return file_put_contents($filename, serialize($data)) !== false;
    }

    public function has(string $key): bool
    {
        $filename = $this->getFilename($key);
        
        if (!file_exists($filename)) {
            return false;
        }

        // Enforce file size limit (1MB)
        if (filesize($filename) > 1048576) {
            error_log('Cache file exceeds size limit: ' . $filename);
            $this->delete($key);
            return false;
        }

        $data = unserialize(file_get_contents($filename), ['allowed_classes' => false]);
        if (!is_array($data)) {
            error_log('Invalid cache data format');
            $this->delete($key);
            return false;
        }
        
        return $data['expire'] === 0 || $data['expire'] >= time();
    }

    public function delete(string $key): bool
    {
        $filename = $this->getFilename($key);
        return file_exists($filename) ? unlink($filename) : true;
    }

    public function clear(): bool
    {
        $files = glob($this->cacheDir . $this->tenantPrefix . '*');
        $success = true;
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $success = $success && unlink($file);
            }
        }

        return $success;
    }

    private function getFilename(string $key): string
    {
        return $this->cacheDir . $this->tenantPrefix . md5($key);
    }
}
