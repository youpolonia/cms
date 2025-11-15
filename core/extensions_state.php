<?php
// Extension state management helper functions
// This provides runtime enforcement for disabled extensions

if (!function_exists('ext_state_load')) {
    /**
     * Load extension state from state.json file
     * @return array Extension state data
     */
    function ext_state_load(): array {
        static $state_cache = null;
        
        if ($state_cache !== null) {
            return $state_cache;
        }
        
        $state_file = dirname(__DIR__) . '/extensions/state.json';
        
        if (!file_exists($state_file)) {
            $state_cache = [];
            return $state_cache;
        }
        
        $json = file_get_contents($state_file);
        if ($json === false) {
            error_log("Failed to read extension state file: $state_file");
            $state_cache = [];
            return $state_cache;
        }
        
        $state = json_decode($json, true);
        if ($state === null) {
            error_log("Failed to parse extension state JSON: $state_file");
            $state_cache = [];
            return $state_cache;
        }
        
        $state_cache = $state;
        return $state_cache;
    }
}

if (!function_exists('ext_is_enabled')) {
    /**
     * Check if an extension is enabled
     * @param string $slug Extension slug (directory name)
     * @return bool True if enabled, false if disabled or unknown
     */
    function ext_is_enabled(string $slug): bool {
        $state = ext_state_load();
        if (!array_key_exists($slug, $state)) {
            return true;
        }
        $val = $state[$slug];
        if (is_bool($val)) {
            return $val;
        }
        if (is_string($val)) {
            return strtolower($val) !== 'disabled';
        }
        return (bool)$val;
    }
}

if (!function_exists('ext_state_save')) {
    /**
     * Persist full extension state map to disk atomically.
     * @param array $states Extension state data
     * @return bool True on success, false on failure
     */
    function ext_state_save(array $states): bool {
        $state_file = dirname(__DIR__) . '/extensions/state.json';
        $dir = dirname($state_file);

        if (!is_dir($dir)) {
            return false;
        }

        $tmp = $state_file . '.tmp';
        $json = json_encode($states, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return false;
        }

        $fp = @fopen($tmp, 'wb');
        if ($fp === false) {
            return false;
        }

        $ok = false;
        if (@flock($fp, LOCK_EX)) {
            $written = @fwrite($fp, $json);
            @fflush($fp);
            @flock($fp, LOCK_UN);
            if ($written !== false) {
                $ok = @rename($tmp, $state_file);
            }
        }
        @fclose($fp);

        if (!$ok) {
            @unlink($tmp);
        }

        return (bool)$ok;
    }
}

if (!function_exists('ext_set_enabled')) {
    /**
     * Set enabled/disabled state for a single extension slug and persist.
     * @param string $slug Extension slug (directory name)
     * @param bool $enabled True to enable, false to disable
     * @return bool True on success, false on failure
     */
    function ext_set_enabled(string $slug, bool $enabled): bool {
        $slug = trim($slug);
        if ($slug === '') {
            return false;
        }

        $states = ext_state_load();
        $states[$slug] = $enabled;
        $saved = ext_state_save($states);

        if ($saved) {
            ext_audit_log($enabled ? 'enable' : 'disable', [
                'slug' => $slug,
                'result' => 'ok',
                'timestamp' => gmdate('c')
            ]);
        }

        return $saved;
    }
}

if (!function_exists('ext_toggle')) {
    /**
     * Toggle the enabled state for a single extension slug and persist.
     * @param string $slug Extension slug (directory name)
     * @return bool True on success, false on failure
     */
    function ext_toggle(string $slug): bool {
        $slug = trim($slug);
        if ($slug === '') {
            return false;
        }

        $current = ext_is_enabled($slug);
        return ext_set_enabled($slug, !$current);
    }
}

if (!function_exists('ext_audit_log')) {
    /**
     * Log extension audit events (if available)
     * @param string $action Action type
     * @param array $data Additional data
     */
    function ext_audit_log(string $action, array $data = []): void {
        $log_file = dirname(__DIR__) . '/logs/extensions.log';
        $log_dir = dirname($log_file);
        
        if (!is_dir($log_dir)) {
            @mkdir($log_dir, 0755, true);
        }
        
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => $action,
            'data' => $data,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $line = json_encode($entry) . "\n";
        @file_put_contents($log_file, $line, FILE_APPEND | LOCK_EX);
    }
}
