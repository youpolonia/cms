<?php
/**
 * File system utilities for FTP-only CMS
 */
function safe_file_get_contents($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $content = @file_get_contents($path);
    return $content !== false ? $content : false;
}

function safe_file_put_contents($path, $data, $flags = 0) {
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    
    return @file_put_contents($path, $data, $flags) !== false;
}

function safe_unlink($path) {
    if (file_exists($path)) {
        return @unlink($path);
    }
    return false;
}

function safe_mkdir($path, $mode = 0755, $recursive = true) {
    if (!is_dir($path)) {
        return @mkdir($path, $mode, $recursive);
    }
    return true;
}

function safe_rmdir($path) {
    if (is_dir($path)) {
        $objects = scandir($path);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                $fullPath = $path . DIRECTORY_SEPARATOR . $object;
                if (is_dir($fullPath)) {
                    safe_rmdir($fullPath);
                } else {
                    safe_unlink($fullPath);
                }
            }
        }
        return @rmdir($path);
    }
    return false;
}

function safe_copy($src, $dest) {
    if (!file_exists($src)) {
        return false;
    }
    
    $dir = dirname($dest);
    if (!is_dir($dir)) {
        safe_mkdir($dir);
    }
    
    return @copy($src, $dest);
}

function safe_rename($old, $new) {
    if (file_exists($old)) {
        return @rename($old, $new);
    }
    return false;
}
