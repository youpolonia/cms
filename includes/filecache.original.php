<?php
/**
 * Simple File-based Cache
 */
class FileCache {
    protected $cacheDir = 'cache';
    protected $ttl = 3600; // 1 hour

    public function __construct() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get($key) {
        $file = $this->cacheDir.'/'.$key;
        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));
        if (time() > $data['expires']) {
            unlink($file);
            return false;
        }

        return $data['content'];
    }

    public function set($key, $content, $ttl = null) {
        $file = $this->cacheDir.'/'.$key;
        $ttl = $ttl ?? $this->ttl;

        $data = [
            'content' => $content,
            'expires' => time() + $ttl
        ];

        file_put_contents($file, serialize($data));
    }

    public function clear($key) {
        $file = $this->cacheDir.'/'.$key;
        if (file_exists($file)) {
            unlink($file);
        }
    }
}
