<?php
class RemoteFetcher {
    const CACHE_DIR = '/data/prompts/cache/';
    const CACHE_TTL = 3600; // 1 hour

    public static function fetchIndex(string $url): array {
        $cacheKey = md5($url) . '.json';
        $cacheFile = self::CACHE_DIR . $cacheKey;

        // Return cached data if valid
        if (file_exists($cacheFile) && 
            (time() - filemtime($cacheFile)) < self::CACHE_TTL) {
            return json_decode(file_get_contents($cacheFile), true);
        }

        // Fetch fresh data
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            throw new Exception("Failed to fetch prompt index");
        }

        $data = json_decode($response, true);
        
        // Validate schema
        if (!isset($data['prompts'])) {
            throw new Exception("Invalid prompt index format");
        }

        // Cache the response atomically
        if (!is_dir(self::CACHE_DIR)) {
            mkdir(self::CACHE_DIR, 0755, true);
        }
        require_once __DIR__ . '/tmp_sandbox.php';
        $tempFile = tempnam(cms_tmp_dir(), 'prompt_');
        file_put_contents($tempFile, $response);
        if (!rename($tempFile, $cacheFile)) {
            unlink($tempFile);
            throw new Exception("Failed to cache prompt index");
        }

        return $data;
    }

    public static function fetchPrompt(string $url): string {
        $cacheKey = md5($url) . '.prompt';
        $cacheFile = self::CACHE_DIR . $cacheKey;

        if (file_exists($cacheFile)) {
            return file_get_contents($cacheFile);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            throw new Exception("Failed to fetch prompt content");
        }

        // Cache atomically
        require_once __DIR__ . '/tmp_sandbox.php';
        $tempFile = tempnam(cms_tmp_dir(), 'prompt_');
        file_put_contents($tempFile, $response);
        rename($tempFile, $cacheFile);

        return $response;
    }
}
