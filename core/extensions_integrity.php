<?php
declare(strict_types=1);

/**
 * Extension integrity verification system
 */

/**
 * Build integrity baseline for an extension
 * 
 * @param string $slug Extension slug
 * @return array ["ok"=>true,"files"=>[rel=>sha256,...]] or ["ok"=>false,"error"=>"..."]
 */
function ext_integrity_build(string $slug): array {
    // Validate slug format
    if (!preg_match('/^[a-z0-9_\-]{3,64}$/', $slug)) {
        return ["ok" => false, "error" => "invalid_slug"];
    }
    
    $root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);
    $extDir = $root . '/extensions/' . $slug;
    
    if (!is_dir($extDir)) {
        return ["ok" => false, "error" => "extension_not_found"];
    }
    
    $files = [];
    
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $absolutePath = $file->getPathname();
                $relativePath = substr($absolutePath, strlen($extDir) + 1);
                
                // Normalize path separators
                $relativePath = str_replace('\\', '/', $relativePath);
                
                $hash = hash_file('sha256', $absolutePath);
                if ($hash === false) {
                    return ["ok" => false, "error" => "hash_failed"];
                }
                
                $files[$relativePath] = $hash;
            }
        }
    } catch (Exception $e) {
        return ["ok" => false, "error" => "scan_failed"];
    }
    
    // Ensure integrity directory exists
    $integrityDir = $root . '/extensions/.integrity';
    if (!is_dir($integrityDir)) {
        if (!@mkdir($integrityDir, 0755, true)) {
            return ["ok" => false, "error" => "write_failed"];
        }
    }
    
    // Atomic write of baseline
    $baselineFile = $integrityDir . '/' . $slug . '.json';
    $tmpFile = $baselineFile . '.tmp';
    
    $data = [
        "slug" => $slug,
        "timestamp" => gmdate('c'),
        "files" => $files
    ];
    
    $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($json === false) {
        return ["ok" => false, "error" => "json_encode_failed"];
    }
    
    if (@file_put_contents($tmpFile, $json, LOCK_EX) === false) {
        return ["ok" => false, "error" => "write_failed"];
    }
    
    if (!@rename($tmpFile, $baselineFile)) {
        @unlink($tmpFile);
        return ["ok" => false, "error" => "write_failed"];
    }
    
    @chmod($baselineFile, 0644);
    
    return ["ok" => true, "files" => $files];
}

/**
 * Check extension integrity against baseline
 * 
 * @param string $slug Extension slug
 * @return array ["ok"=>true] or ["ok"=>false,"mismatch"=>[rel=>["expected"=>..., "actual"=>...]], "missing"=>[...], "extra"=>[...]]
 */
function ext_integrity_check(string $slug): array {
    // Validate slug format
    if (!preg_match('/^[a-z0-9_\-]{3,64}$/', $slug)) {
        return ["ok" => false, "error" => "invalid_slug"];
    }
    
    $root = defined('CMS_ROOT') ? CMS_ROOT : dirname(__DIR__);
    $extDir = $root . '/extensions/' . $slug;
    $baselineFile = $root . '/extensions/.integrity/' . $slug . '.json';
    
    if (!is_dir($extDir)) {
        return ["ok" => false, "error" => "extension_not_found"];
    }
    
    if (!is_file($baselineFile)) {
        return ["ok" => false, "error" => "no_baseline"];
    }
    
    // Load baseline
    $content = @file_get_contents($baselineFile);
    if ($content === false) {
        return ["ok" => false, "error" => "baseline_read_failed"];
    }
    
    $baseline = @json_decode($content, true);
    if (!is_array($baseline) || !isset($baseline['files']) || !is_array($baseline['files'])) {
        return ["ok" => false, "error" => "baseline_corrupt"];
    }
    
    $expectedFiles = $baseline['files'];
    $currentFiles = [];
    
    // Scan current files
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($extDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $absolutePath = $file->getPathname();
                $relativePath = substr($absolutePath, strlen($extDir) + 1);
                
                // Normalize path separators
                $relativePath = str_replace('\\', '/', $relativePath);
                
                $hash = hash_file('sha256', $absolutePath);
                if ($hash === false) {
                    return ["ok" => false, "error" => "hash_failed"];
                }
                
                $currentFiles[$relativePath] = $hash;
            }
        }
    } catch (Exception $e) {
        return ["ok" => false, "error" => "scan_failed"];
    }
    
    // Compare files
    $mismatch = [];
    $missing = [];
    $extra = [];
    
    // Check for missing and mismatched files
    foreach ($expectedFiles as $file => $expectedHash) {
        if (!isset($currentFiles[$file])) {
            $missing[] = $file;
        } elseif ($currentFiles[$file] !== $expectedHash) {
            $mismatch[$file] = [
                "expected" => $expectedHash,
                "actual" => $currentFiles[$file]
            ];
        }
    }
    
    // Check for extra files
    foreach ($currentFiles as $file => $hash) {
        if (!isset($expectedFiles[$file])) {
            $extra[] = $file;
        }
    }
    
    // Return result
    if (empty($mismatch) && empty($missing) && empty($extra)) {
        return ["ok" => true];
    } else {
        $result = ["ok" => false];
        if (!empty($mismatch)) {
            $result["mismatch"] = $mismatch;
        }
        if (!empty($missing)) {
            $result["missing"] = $missing;
        }
        if (!empty($extra)) {
            $result["extra"] = $extra;
        }
        return $result;
    }
}
