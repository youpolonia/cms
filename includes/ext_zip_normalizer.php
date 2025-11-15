<?php
function normalize_extension_zip(string $zipPath, ?string &$slugOut, ?string &$error) {
    $error = null; $slugOut = null;
    if (!is_file($zipPath) || filesize($zipPath) < 4) { $error='zip_not_found_or_empty'; return false; }
    $z = new ZipArchive(); if ($z->open($zipPath) !== true) { $error='zip_open_failed'; return false; }
    $root = $z->locateName('extension.json', ZipArchive::FL_NOCASE|ZipArchive::FL_NODIR);
    $slugFolder = null;
    if ($root === false) {
        for ($i=0; $i<$z->numFiles; $i++) {
            $n = $z->getNameIndex($i); if ($n === false) continue;
            if (preg_match('~^([^/]+)/extension\.json$~i', $n, $m)) { $slugFolder=$m[1]; $root=$i; break; }
        }
    }
    $manifestJson = ($root !== false) ? $z->getFromIndex($root) : null;
    if (!is_string($manifestJson) || $manifestJson==='') { $z->close(); $error='manifest_missing_or_empty'; return false; }
    $manifest = json_decode($manifestJson, true);
    if (!is_array($manifest)) { $z->close(); $error='manifest_invalid_json'; return false; }
    if (!isset($manifest['slug']) || !is_string($manifest['slug']) || $manifest['slug']==='') { $z->close(); $error='manifest_missing_slug'; return false; }
    $slug = preg_replace('~[^a-z0-9\-]~i','-',$manifest['slug']); $slug = trim($slug,'-');
    if ($slug==='') { $z->close(); $error='slug_normalized_empty'; return false; }
    $slugOut = $slug;
    if ($slugFolder !== null && strcasecmp($slugFolder,$slug)===0) { $z->close(); return $zipPath; }
    $prefix = ($slugFolder !== null) ? $slugFolder.'/' : '';
    require_once __DIR__ . '/../core/tmp_sandbox.php';
    $tmpBase = cms_tmp_dir(); $workDir = $tmpBase.'/extnorm_'.bin2hex(random_bytes(6)); $slugDir = $workDir.'/'.$slug;
    if (!mkdir($slugDir,0777,true)) { $z->close(); $error='workdir_create_failed'; return false; }
    for ($i=0; $i<$z->numFiles; $i++) {
        $n = $z->getNameIndex($i); if ($n === false) continue;
        $isDir = substr($n,-1)==='/'; $local = $n;
        if ($prefix !== '' && strpos($local,$prefix)===0) { $local = substr($local, strlen($prefix)); }
        $local = ltrim($local,'/'); if ($local==='') continue;
        $tp = $slugDir.'/'.$local;
        if ($isDir) { if (!is_dir($tp) && !mkdir($tp,0777,true)) { $z->close(); $error='extract_dir_failed'; return false; } continue; }
        $data = $z->getFromIndex($i); if ($data === false) { $z->close(); $error='extract_file_failed'; return false; }
        $pp = dirname($tp); if (!is_dir($pp) && !mkdir($pp,0777,true)) { $z->close(); $error='extract_parent_failed'; return false; }
        if (file_put_contents($tp,$data) === false) { $z->close(); $error='write_failed'; return false; }
    }
    $z->close();
    $normZip = $tmpBase.'/extnorm_'.$slug.'_'.bin2hex(random_bytes(4)).'.zip';
    $nz = new ZipArchive(); if ($nz->open($normZip, ZipArchive::CREATE|ZipArchive::OVERWRITE)!==true) { $error='norm_zip_open_failed'; return false; }
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($rii as $fs) { $fsPath=(string)$fs; $local=substr($fsPath, strlen($workDir)+1); $local=str_replace('\\','/',$local); if (is_dir($fsPath)) { $nz->addEmptyDir($local); } else { $nz->addFile($fsPath,$local); } }
    $nz->close();
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($workDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($it as $p) { $p->isDir()?@rmdir($p->getPathname()):@unlink($p->getPathname()); } @rmdir($workDir);
    return $normZip;
}
