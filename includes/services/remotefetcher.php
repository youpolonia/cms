<?php
/**
 * Remote JSON Fetcher Service
 * Handles fetching and caching of remote plugin index
 */
class RemoteFetcher {
    private $cacheDir;
    private $cacheTtl = 3600; // 1 hour cache
    
    public function __construct() {
        $this->cacheDir = __DIR__ . '/../../storage/cache/remote/';
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Fetch remote JSON with caching
     */
    public function fetch($url) {
        $cacheFile = $this->cacheDir . md5($url) . '.json';
        
        // Return cached data if valid
        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            if (time() - $cacheTime < $this->cacheTtl) {
                return json_decode(file_get_contents($cacheFile), true);
            }
        }

        // Fetch fresh data
        $data = $this->fetchRemote($url);
        if ($data) {
            file_put_contents($cacheFile, json_encode($data));
            return $data;
        }

        // Fallback to cache if available
        if (file_exists($cacheFile)) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        return null;
    }

    private function fetchRemote($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            error_log('Remote fetch error: ' . curl_error($ch));
            return null;
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }
}
