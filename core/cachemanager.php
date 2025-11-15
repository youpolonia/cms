<?php
class CacheManager {
    const CACHE_EXPIRY_MINUTES = 10;
    
    /**
     * Get cached data or regenerate if expired
     * @param string $cacheFile Path to cache file
     * @param callable $generator Function to generate fresh data
     * @return array Cached data
     */
    public static function get(string $cacheFile, callable $generator): array {
        $cached = self::readCache($cacheFile);
        
        if (self::isValid($cached)) {
            return $cached['data'];
        }
        
        $data = $generator();
        self::writeCache($cacheFile, $data);
        return $data;
    }
    
    private static function readCache(string $file): array {
        if (!file_exists($file)) {
            return ['expires' => 0, 'data' => []];
        }
        
        return require_once $file;
    }
    
    private static function isValid(array $cache): bool {
        return isset($cache['expires']) && 
               $cache['expires'] > time() && 
               isset($cache['data']);
    }
    
    private static function writeCache(string $file, array $data): void {
        $content = "<?php\n// Auto-generated cache file - DO NOT EDIT\n// Generated: " . date('Y-m-d H:i:s') . "\nreturn [\n";
        $content .= "    'expires' => " . (time() + (self::CACHE_EXPIRY_MINUTES * 60)) . ",\n";
        $content .= "    'data' => " . var_export($data, true) . "\n];";
        
        file_put_contents($file, $content);
    }
}
