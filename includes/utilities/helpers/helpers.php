<?php
/**
 * Helper functions for the CMS
 */

require_once __DIR__ . '/FileCache.php';
require_once __DIR__ . '/Config/ConfigLoader.php';
require_once __DIR__ . '/../../errorhandler.php';

if (!function_exists('get_env_var')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_env_var(string $key, $default = null)
    {
        // First check $_ENV superglobal
        $value = $_ENV[$key] ?? null;
        
        // Then check getenv() if not found
        if ($value === null) {
            $value = getenv($key);
        }
        
        // Return default if not found
        if ($value === false) {
            return $default;
        }

        // Convert string values to appropriate types
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        // Handle numeric values
        if (is_numeric($value)) {
            return strpos($value, '.') === false ? (int)$value : (float)$value;
        }

        return $value;
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * Generate CSRF token and store in session
     * @return string
     */
    function generate_csrf_token(): string
    {
        static $cache = null;
        if ($cache === null) {
            $cache = new \Core\Cache\SessionCacheAdapter(
                \Core\Cache\CacheFactory::make(),
                session_id()
            );
        }
        
        $token = $cache->get(session_id(), 'csrf_token');
        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            $cache->set(session_id(), 'csrf_token', $token);
        }
        return $token;
    }
}

if (!function_exists('get_csrf_meta_tag')) {
    /**
     * Generate CSRF meta tag
     * @return string
     */
    function get_csrf_meta_tag(): string
    {
        return '
<meta name="csrf-token" content="' . generate_csrf_token() . '">';
    }
}

/**
 * Simple debug helper
 */
if (!function_exists('dd')) {
    function dd(...$vars) {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('generate_schema_summary')) {
    /**
     * Creates compact representation of migration schemas
     *
     * @param array $migrationContent Full migration file content
     * @return string Compact schema summary
     */
    function generate_schema_summary(array $migrationContent): string {
        $summary = [];
        foreach ($migrationContent as $table => $columns) {
            $columnTypes = [];
            foreach ($columns as $column => $def) {
                $columnTypes[] = $column . ':' . $def['type'];
            }
            $summary[] = $table . '[' . implode(',', $columnTypes) . ']';
        }
        return implode(';', $summary);
    }
}

if (!function_exists('estimate_token_usage')) {
    /**
     * Estimates token count for analysis tasks
     *
     * @param string $content Content to analyze
     * @return int Approximate token count (4 chars ≈ 1 token)
     */
    function estimate_token_usage(string $content): int {
        return ceil(strlen($content) / 4);
    }
}

if (!function_exists('chunk_migrations')) {
    /**
     * Groups related migrations into chunks (max 3-4 per chunk)
     *
     * @param array $migrations List of migration file paths
     * @return array Chunked migrations
     */
    function chunk_migrations(array $migrations): array {
        $chunks = [];
        $currentChunk = [];
        $currentSize = 0;
        
        foreach ($migrations as $migration) {
            $content = file_get_contents($migration);
            $size = estimate_token_usage($content);
            
            if ($currentSize + $size > 50000 || count($currentChunk) >= 4) {
                $chunks[] = $currentChunk;
                $currentChunk = [];
                $currentSize = 0;
            }
            
            $currentChunk[] = $migration;
            $currentSize += $size;
        }
        
        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }
        
        return $chunks;
    }
}
