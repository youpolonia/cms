<?php
declare(strict_types=1);

// Debug logging setup
function ext_debug_log(string $message): void {
    if (!defined('CMS_ROOT')) {
        define('CMS_ROOT', dirname(__DIR__));
    }
    file_put_contents(CMS_ROOT . '/logs/ext_debug.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

// Security logging
function ext_security_log(string $message): void {
    if (!defined('CMS_ROOT')) {
        define('CMS_ROOT', dirname(__DIR__));
    }
    file_put_contents(CMS_ROOT . '/logs/ext_security.log', date('[Y-m-d H:i:s] ') . $message . PHP_EOL, FILE_APPEND);
}

/**
 * Safe ZIP extraction for extensions with security validation
 * 
 * @param string $zipPath Path to the ZIP file
 * @param string $slug Expected extension slug
 * @param string $destDir Final destination directory
 * @param string $stagingDir Temporary staging directory
 * @param array $limits Extraction limits
 * @return array [bool success, string|null error_code]
 */
function ext_zip_extract_safe(string $zipPath, string $slug, string $destDir, string $stagingDir, array $limits = []): array {
    // Validate slug format
    if (!preg_match('/^[a-z0-9_\-]{3,64}$/', $slug)) {
        return [false, 'invalid_slug'];
    }
    
    // Open ZIP archive
    if (!class_exists('ZipArchive')) {
        return [false, 'zip_not_available'];
    }
    
    $zip = new ZipArchive();
    ext_debug_log("Opening ZIP file: " . $zipPath);
    $result = $zip->open($zipPath);
    if ($result !== true) {
        ext_debug_log("Failed to open ZIP: " . $result);
        return [false, 'zip_open_failed'];
    }
    ext_debug_log("ZIP opened successfully with " . $zip->numFiles . " files");
    
    $numFiles = $zip->numFiles;
    $total = 0;
    $files = 0;
    $sawTopLevelManifest = false;
    $foundTraversal = false;
    
    // 1) SCAN ONLY - First pass validation
    for ($i = 0; $i < $numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if ($stat === false) {
            $zip->close();
            return [false, 'stat_failed'];
        }
        
        $name = $stat['name'];
        
        // Normalize
        $rel = str_replace('\\', '/', $name);
        while (strpos($rel, '//') !== false) {
            $rel = str_replace('//', '/', $rel);
        }
        $rel = preg_replace('~^\./+~', '', $rel);
        
        // Check for traversal candidates (but continue scan)
        if ($rel === '' ||
            $rel[0] === '/' ||
            preg_match('~^[A-Za-z]:/~', $rel) ||
            strpos($rel, "\0") !== false ||
            preg_match('~(^|/)\.\.(?:/|$)~', $rel)) {
            $foundTraversal = true;
        }
        
        // Detect symlink (skip if external_attr not available)
        if (!isset($stat['external_attr'])) {
            ext_debug_log("No external_attr available for file: " . $rel);
            continue;
        }
        $mode = (($stat['external_attr'] >> 16) & 0xF000);
        if ($mode === 0xA000) {
            $zip->close();
            return [false, 'symlink_detected'];
        }
        
        // Check for top-level extension.json (case-insensitive)
        ext_debug_log("Checking file: " . $rel . " (normalized: " . strtolower($rel) . ")");
        if (strtolower($rel) === 'extension.json') {
            $sawTopLevelManifest = true;
            ext_debug_log("Found manifest at index $i");
        }
        
        // Enforce limits - count only regular files for bytes
        if (substr($rel, -1) !== '/') {
            $total += (int)($stat['size']);
            $files++;
            
            if ($files > ($limits['MAX_FILES'] ?? 500)) {
                $zip->close();
                return [false, 'too_many_files'];
            }
            
            if ($total > ($limits['MAX_TOTAL_BYTES'] ?? 8*1024*1024)) {
                $zip->close();
                return [false, 'too_large'];
            }
        }
    }
    
    // 2) After scan completes - check traversal first, then manifest
    if ($foundTraversal) {
        $zip->close();
        return [false, 'path_traversal'];
    }
    
    if (!$sawTopLevelManifest) {
        $zip->close();
        return [false, 'missing_manifest'];
    }
    
    // Read and validate manifest - only now after security checks
    $manifest = $zip->getFromName('extension.json');
    if ($manifest === false) {
        $zip->close();
        return [false, 'missing_manifest'];
    }
    
    $j = json_decode($manifest, true);
    if (!is_array($j) || ($j['slug'] ?? '') !== $slug) {
        $zip->close();
        return [false, 'slug_mismatch'];
    }
    
    // 3) EXTRACT - Second pass
    $base = rtrim($stagingDir, '/') . '/' . $slug . '-' . bin2hex(random_bytes(4));
    if (!@mkdir($base, 0755, true)) {
        $zip->close();
        return [false, 'write_failed'];
    }
    
    // Iterate entries again for extraction
    for ($i = 0; $i < $numFiles; $i++) {
        $stat = $zip->statIndex($i);
        if ($stat === false) {
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'stat_failed'];
        }
        
        $name = $stat['name'];
        
        // Normalize (same as scan pass)
        $rel = str_replace('\\', '/', $name);
        while (strpos($rel, '//') !== false) {
            $rel = str_replace('//', '/', $rel);
        }
        $rel = preg_replace('~^\./+~', '', $rel);
        
        // Build safe path from canonical segments
        $canon = [];
        foreach (explode('/', $rel) as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                $zip->close();
                ext_zip_cleanup_recursive($base);
                return [false, 'path_traversal'];
            }
            $canon[] = $seg;
        }
        $safePath = implode('/', $canon);
        $dest = $base . '/' . $safePath;
        
        // Verify path containment
        $realBase = realpath($base);
        $realDest = realpath(dirname($dest));
        if ($realBase === false || $realDest === false) {
            ext_security_log("Path resolution failed for base: $base, dest: $dest");
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'path_traversal'];
        }
        if (strpos($realDest, $realBase) !== 0) {
            ext_security_log("Path traversal attempt detected: $realDest not within $realBase");
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'path_traversal'];
        }
        ext_debug_log("Path validated: $realDest is within $realBase");
        
        // Handle directories
        if (substr($rel, -1) === '/') {
            if (!is_dir($dest) && !@mkdir($dest, 0755, true)) {
                $zip->close();
                ext_zip_cleanup_recursive($base);
                return [false, 'write_failed'];
            }
            continue;
        }
        
        // Handle files
        $d = dirname($dest);
        if (!is_dir($d) && !@mkdir($d, 0755, true)) {
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'write_failed'];
        }
        
        $stream = $zip->getStream($name);
        if (!$stream) {
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'write_failed'];
        }
        
        $out = fopen($dest, 'wb');
        if (!$out) {
            fclose($stream);
            $zip->close();
            ext_zip_cleanup_recursive($base);
            return [false, 'write_failed'];
        }
        
        stream_copy_to_stream($stream, $out);
        fclose($out);
        fclose($stream);
        chmod($dest, 0644);
    }
    
    $zip->close();
    
    // Atomically move to final destination
    $final = rtrim($destDir, '/') . '/' . $slug;
    
    if (is_dir($final)) {
        ext_zip_cleanup_recursive($base);
        return [false, 'already_installed'];
    }
    
    // Ensure destination directory exists
    if (!is_dir($destDir)) {
        if (!@mkdir($destDir, 0755, true)) {
            ext_zip_cleanup_recursive($base);
            return [false, 'write_failed'];
        }
    }
    
    if (!@rename($base, $final)) {
        ext_zip_cleanup_recursive($base);
        return [false, 'write_failed'];
    }
    
    // Set final directory permissions
    @chmod($final, 0755);
    
    return [true, null];
}

/**
 * Recursively remove directory and all contents
 * 
 * @param string $dir Directory path to remove
 * @return void
 */
function ext_zip_cleanup_recursive(string $dir): void {
    if (!is_dir($dir)) {
        return;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isDir()) {
            @rmdir($file->getPathname());
        } else {
            @unlink($file->getPathname());
        }
    }
    
    @rmdir($dir);
}
