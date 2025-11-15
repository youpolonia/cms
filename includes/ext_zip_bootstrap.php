<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['extension']['tmp_name']) && is_uploaded_file($_FILES['extension']['tmp_name'])) {
    $orig = $_FILES['extension']['tmp_name'];
    
    // Validate extension
    $pathInfo = pathinfo($_FILES['extension']['name'] ?? '');
    $extension = strtolower($pathInfo['extension'] ?? '');
    if ($extension !== 'zip') {
        http_response_code(400);
        error_log('Invalid extension package: not a zip file');
        exit;
    }
    
    // Validate MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($orig);
    if ($mimeType !== 'application/zip') {
        http_response_code(400);
        error_log('Invalid extension package: MIME type ' . $mimeType);
        exit;
    }
    
    // Verify ZIP signature
    $signature = substr(file_get_contents($orig, false, null, 0, 4), 0, 4);
    if ($signature !== "PK\x03\x04") {
        http_response_code(400);
        error_log('Invalid extension package: invalid ZIP signature');
        exit;
    }
    
    // Generate random temp filename in safe directory
    require_once __DIR__ . '/../core/tmp_sandbox.php';
    $tempDir = realpath(cms_tmp_dir());
    if (!$tempDir) {
        http_response_code(500);
        error_log('Temp directory not accessible');
        exit;
    }
    
    $randomName = bin2hex(random_bytes(16)) . '.zip';
    $norm = $tempDir . '/' . $randomName;
    
    // Ensure target is within temp directory
    if (strncmp($norm, $tempDir, strlen($tempDir)) !== 0) {
        http_response_code(400);
        error_log('Path traversal attempt in extension upload');
        exit;
    }
    
    $err = null; $slug = null;
    $norm = normalize_extension_zip($orig, $slug, $err);
    if ($norm === false) {
        if (function_exists('ext_audit_log')) { 
            ext_audit_log('extension_install_failed', ['file'=>basename($_FILES['extension']['name']??''), 'size'=>@filesize($orig), 'error'=>$err]); 
        }
        header('Location: upload.php', true, 303); 
        exit;
    }
    
    $data = @file_get_contents($norm);
    if ($data === false || @file_put_contents($orig, $data) === false) {
        if (function_exists('ext_audit_log')) { 
            ext_audit_log('extension_install_failed', ['file'=>basename($_FILES['extension']['name']??''), 'size'=>@filesize($orig), 'error'=>'overwrite_tmp_failed']); 
        }
        header('Location: upload.php', true, 303); 
        exit;
    }
    
    $_FILES['extension']['size'] = strlen($data);
    $_POST['__ext_norm_slug'] = $slug;
    @unlink($norm);
}
