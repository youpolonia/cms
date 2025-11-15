<?php
class CacheService {
    private $cacheDir;
    private $useMemoryCache;

    public function __construct() {
        $this->cacheDir = __DIR__.'/../../storage/cache/';
        $this->useMemoryCache = function_exists('apcu_enabled') && apcu_enabled();
        
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Get cached item
     */
    public function get($key) {
        // Try memory cache first
        if ($this->useMemoryCache && function_exists('apcu_fetch')) {
            $value = apcu_fetch($key);
            if ($value !== false) {
                return $value;
            }
        }

        // Fall back to file cache
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            // Enforce file size limit (1MB)
            if (filesize($file) > 1048576) {
                error_log('Cache file exceeds size limit: ' . $file);
                unlink($file);
                return null;
            }
            
            $data = unserialize(file_get_contents($file), ['allowed_classes' => false]);
            if (!is_array($data)) {
                error_log('Invalid cache data format');
                unlink($file);
                return null;
            }
            
            if ($data['expires'] > time()) {
                // Store in memory cache for next request
                if ($this->useMemoryCache) {
                    apcu_store($key, $data['value'], $data['expires'] - time());
                }
                return $data['value'];
            }
            unlink($file);
        }
        return null;
    }

    /**
     * Store item in cache
     */
    public function set($key, $value, $ttl = 3600) {
        $expires = time() + $ttl;
        $data = ['value' => $value, 'expires' => $expires];
        
        // Store in memory cache
        if ($this->useMemoryCache) {
            apcu_store($key, $value, $ttl);
        }
        
        // Store in file cache
        file_put_contents(
            $this->getCacheFile($key),
            serialize($data),
            LOCK_EX
        );
    }

    /**
     * Delete cached item
     */
    public function delete($key) {
        if ($this->useMemoryCache) {
            apcu_delete($key);
        }
        
        $file = $this->getCacheFile($key);
        if (file_exists($file)) {
            unlink($file);
        }
    }

    /**
     * Generate cache key for version-related data
     */
    public function getVersionKey($type, $id) {
        return "version_{$type}_{$id}";
    }

    /**
     * Clear all version-related caches for a content item
     */
    public function clearVersionCaches($contentId) {
        $keys = [
            $this->getVersionKey('list', $contentId),
            $this->getVersionKey('filtered', $contentId),
            $this->getVersionKey('count', $contentId)
        ];
        
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }

    private function getCacheFile($key) {
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->cacheDir . $safeKey . '.cache';
    }
}
