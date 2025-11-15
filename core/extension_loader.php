<?php
/**
 * Extension Loader with Runtime Enforcement
 * This file handles loading extensions with state-based enable/disable controls
 */

// Include the extensions state helper
require_once __DIR__ . '/extensions_state.php';

/**
 * Load all enabled extensions from the extensions directory
 */
function load_extensions() {
    $extensions_dir = __DIR__ . '/../extensions';
    
    if (!is_dir($extensions_dir)) {
        error_log("Extensions directory not found: $extensions_dir");
        return;
    }
    
    // Get all extension directories
    $extension_dirs = array_filter(glob("$extensions_dir/*"), 'is_dir');
    
    foreach ($extension_dirs as $dir) {
        $slug = basename($dir);
        
        // Runtime enforcement: check if extension is enabled
        if (!ext_is_enabled($slug)) {
            ext_audit_log('extension_load_skipped', [
                'extension' => $slug, 
                'reason' => 'disabled'
            ]);
            continue; // Skip loading disabled extensions
        }
        
        // Look for bootstrap file
        $bootstrap_file = "$dir/bootstrap.php";
        if (!file_exists($bootstrap_file)) {
            error_log("Extension $slug missing bootstrap.php file");
            continue;
        }
        
        // Load extension manifest for validation
        $manifest_file = "$dir/extension.json";
        if (!file_exists($manifest_file)) {
            error_log("Extension $slug missing extension.json manifest");
            continue;
        }
        
        $manifest = json_decode(file_get_contents($manifest_file), true);
        if (!$manifest || !isset($manifest['slug']) || $manifest['slug'] !== $slug) {
            error_log("Extension $slug has invalid manifest");
            continue;
        }
        
        // Load the extension bootstrap file
        try {
            require_once $bootstrap_file;
            ext_audit_log('extension_loaded', [
                'extension' => $slug,
                'version' => $manifest['version'] ?? 'unknown'
            ]);
        } catch (Throwable $e) {
            error_log("Failed to load extension $slug: " . $e->getMessage());
            ext_audit_log('extension_load_failed', [
                'extension' => $slug,
                'error' => $e->getMessage()
            ]);
        }
    }
}

// Auto-load extensions when this file is included (unless prevented)
if (!defined('PREVENT_EXTENSION_AUTOLOAD')) {
    load_extensions();
}
