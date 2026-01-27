<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/flashmessage.php';

if (!defined('CMS_ROOT')) {
    // admin/extensions/ -> up 2 levels = project root
    define('CMS_ROOT', dirname(__DIR__, 2));
}

final class ExtensionInstaller
{
    private string $uploadDir;   // e.g. CMS_ROOT . '/extensions'
    private string $tempDir;     // e.g. CMS_ROOT . '/uploads/tmp'
    private array $allowedExtensions = ['zip'];
    private array $allowedMimeTypes = [
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream'
    ];
    private int $maxFileSize = 10485760; // 10 MB (form ogranicza do 2 MB, ale zostawiamy zapas)

    // Back-compat: args optional; default to sane directories under CMS_ROOT
    public function __construct(?string $uploadDir = null, ?string $tempDir = null)
    {
        $this->uploadDir = rtrim($uploadDir ?: CMS_ROOT . '/extensions', '/') . '/';
        $this->tempDir   = rtrim($tempDir   ?: CMS_ROOT . '/uploads/tmp',  '/') . '/';

        $this->ensureDir($this->uploadDir);
        $this->ensureDir($this->tempDir);
    }

    private function ensureDir(string $dir): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    private function rrmdir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }
        @rmdir($dir);
    }

    private function safeJoin(string $base, string $rel): string
    {
        $rel = str_replace('\\', '/', $rel);
        if (strpos($rel, "\0") !== false) throw new RuntimeException('Invalid path (NUL).');
        if ($rel === '' || $rel === '.' || $rel === './') return $base;
        // absolute/drive-letter guards
        if ($rel[0] === '/' || preg_match('~^[A-Za-z]:[/\\\\]~', $rel)) {
            throw new RuntimeException('Invalid path (absolute).');
        }
        // normalize
        $parts = [];
        foreach (explode('/', $rel) as $seg) {
            if ($seg === '' || $seg === '.') continue;
            if ($seg === '..') { array_pop($parts); continue; }
            $parts[] = $seg;
        }
        $joined = $base . '/' . implode('/', $parts);
        $realBase = realpath($base) ?: $base;
        $realDir  = realpath(dirname($joined)) ?: dirname($joined);
        // ensure stays under base
        if (strpos($realDir, rtrim($realBase, '/')) !== 0) {
            throw new RuntimeException('Path traversal detected.');
        }
        return $joined;
    }

    public function validateFile(array $file): string
    {
        if (!isset($file['error'], $file['name'], $file['tmp_name'], $file['size'])) {
            return 'Invalid upload structure.';
        }
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            return 'File upload error: ' . (int)$file['error'];
        }
        if ($file['size'] > $this->maxFileSize) {
            return 'File size exceeds maximum allowed.';
        }
        $fileExt = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExt, $this->allowedExtensions, true)) {
            return 'Invalid file extension.';
        }
        if (!is_uploaded_file($file['tmp_name'])) {
            return 'Invalid temporary upload.';
        }
        $mime = '';
        if (function_exists('finfo_open')) {
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            if ($fi) {
                $mime = (string)finfo_file($fi, $file['tmp_name']);
                finfo_close($fi);
            }
        }
        if ($mime && !in_array($mime, $this->allowedMimeTypes, true)) {
            return 'Invalid file type.';
        }
        if (!class_exists('ZipArchive')) {
            return 'Zip support not available on server.';
        }
        return '';
    }

    private function readManifest(\ZipArchive $zip): array
    {
        // locate extension.json (case-insensitive, non-directory)
        $idx = $zip->locateName('extension.json', \ZipArchive::FL_NODIR | \ZipArchive::FL_NOCASE);
        if ($idx === false) {
            // try to find any */extension.json
            $found = -1;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st = $zip->statIndex($i);
                if (!$st) continue;
                $name = $st['name'] ?? '';
                if (preg_match('~(^|/)+extension\.json$~i', $name)) { $found = $i; break; }
            }
            if ($found === -1) {
                throw new RuntimeException('Missing extension.json manifest.');
            }
            $idx = $found;
        }
        $st = $zip->statIndex($idx);
        $manifestDir = '';
        if ($st && isset($st['name'])) {
            $p = str_replace('\\', '/', $st['name']);
            $manifestDir = trim(dirname($p), '.\\/'); // '' if in root
        }
        $stream = $zip->getStream($st['name']);
        if (!$stream) throw new RuntimeException('Cannot read manifest.');
        $json = stream_get_contents($stream);
        fclose($stream);
        $data = json_decode((string)$json, true);
        if (!is_array($data)) throw new RuntimeException('Invalid manifest JSON.');
        $slug = (string)($data['slug'] ?? '');
        $name = (string)($data['name'] ?? '');
        $ver  = (string)($data['version'] ?? '');
        if ($slug === '' || !preg_match('/^[a-z0-9_\-]{3,64}$/', $slug)) {
            throw new RuntimeException('Invalid or missing slug in manifest.');
        }
        if ($name === '' || $ver === '') {
            throw new RuntimeException('Invalid manifest (name/version).');
        }
        return ['manifestDir' => $manifestDir, 'slug' => $slug, 'data' => $data];
    }

    public function report(string $message, string $type = FlashMessage::TYPE_ERROR): void
    {
        FlashMessage::add($message, $type);
    }

    public function install(array $file): bool
    {
        $err = $this->validateFile($file);
        if ($err !== '') { $this->report($err); return false; }

        $uploadTmp = $this->tempDir . 'ext_' . bin2hex(random_bytes(8)) . '.zip';
        if (!@move_uploaded_file($file['tmp_name'], $uploadTmp)) {
            $this->report('Failed to move uploaded file.');
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($uploadTmp) !== true) {
            @unlink($uploadTmp);
            $this->report('Failed to open ZIP.');
            return false;
        }

        try {
            // Pre-scan entries: totals & traversal, deny symlinks by not extracting metadata
            $maxFiles = 500;
            $maxBytes = 10 * 1024 * 1024; // 10 MB extracted total
            $count = 0; $sum = 0;

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st = $zip->statIndex($i);
                if (!$st) continue;
                $name = (string)($st['name'] ?? '');
                $name = str_replace('\\', '/', $name);
                if ($name === '' || substr($name, -1) === '/') { // directory
                    continue;
                }
                if (strpos($name, "\0") !== false) throw new RuntimeException('Invalid ZIP entry.');
                if ($name[0] === '/' || preg_match('~^[A-Za-z]:[/\\\\]~', $name)) {
                    throw new RuntimeException('ZIP contains absolute paths.');
                }
                if (preg_match('~/\.\./~', '/'.$name.'/')) {
                    throw new RuntimeException('ZIP contains traversal sequences.');
                }
                $count++;
                $sum += (int)($st['size'] ?? 0);
                if ($count > $maxFiles) throw new RuntimeException('ZIP too many files.');
                if ($sum > $maxBytes) throw new RuntimeException('ZIP total size too large.');
            }

            // Read manifest and decide root inside ZIP
            $meta = $this->readManifest($zip);
            $rootInside = $meta['manifestDir']; // '' or e.g. "myext"
            $slug = $meta['slug'];
            $targetDir = rtrim($this->uploadDir, '/') . '/' . $slug;

            if (is_dir($targetDir)) {
                throw new RuntimeException('Extension already installed: ' . $slug);
            }

            // Stage into temp dir, then rename atomically
            $staging = $this->tempDir . 'stage_' . $slug . '_' . bin2hex(random_bytes(6));
            $this->ensureDir($staging);

            // Extract only subtree under manifest dir (or all if manifest in root)
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $st = $zip->statIndex($i);
                if (!$st) continue;
                $name = str_replace('\\', '/', (string)$st['name']);
                $isDir = substr($name, -1) === '/';

                // keep only files under rootInside (if set)
                if ($rootInside !== '') {
                    if (strpos($name, $rootInside . '/') !== 0 && $name !== $rootInside . '/extension.json') {
                        continue;
                    }
                    $rel = ltrim(substr($name, strlen($rootInside)), '/');
                } else {
                    $rel = ltrim($name, '/');
                }

                if ($rel === '' || $rel === '/') continue; // skip bare root
                $dest = $this->safeJoin($staging, $rel);

                if ($isDir) {
                    $this->ensureDir($dest);
                    @chmod($dest, 0755);
                    continue;
                }

                // write file from stream (prevents symlink/hardlink tricks)
                $stream = $zip->getStream($st['name']);
                if (!$stream) throw new RuntimeException('Failed reading ZIP entry: ' . $rel);
                $this->ensureDir(dirname($dest));
                $out = @fopen($dest, 'wb');
                if (!$out) { fclose($stream); throw new RuntimeException('Cannot write file: ' . $rel); }
                stream_copy_to_stream($stream, $out);
                fclose($stream);
                fclose($out);
                @chmod($dest, 0644);
            }

            // Verify manifest exists in staging
            $manifestPath = file_exists($staging . '/extension.json')
                ? $staging . '/extension.json'
                : null;
            if (!$manifestPath) {
                // Try locate inside staging (e.g., manifest under subdir)
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($staging, FilesystemIterator::SKIP_DOTS));
                foreach ($it as $f) {
                    if (strtolower($f->getFilename()) === 'extension.json') {
                        $manifestPath = $f->getPathname();
                        break;
                    }
                }
                if (!$manifestPath) throw new RuntimeException('Manifest missing after extraction.');
            }

            // Finalize: move staging to target
            if (!@rename($staging, $targetDir)) {
                // fallback copy if cross-filesystem
                $this->ensureDir($targetDir);
                $it = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($staging, FilesystemIterator::SKIP_DOTS),
                    RecursiveIteratorIterator::SELF_FIRST
                );
                foreach ($it as $item) {
                    $dst = $this->safeJoin($targetDir, substr($item->getPathname(), strlen($staging) + 1));
                    if ($item->isDir()) {
                        $this->ensureDir($dst);
                        @chmod($dst, 0755);
                    } else {
                        $this->ensureDir(dirname($dst));
                        @copy($item->getPathname(), $dst);
                        @chmod($dst, 0644);
                    }
                }
                $this->rrmdir($staging);
            }

            $this->report('Extension installed successfully: ' . $slug, FlashMessage::TYPE_SUCCESS);
            return true;

        } catch (Throwable $e) {
            $this->report($e->getMessage(), FlashMessage::TYPE_ERROR);
            return false;

        } finally {
            $zip->close();
            @unlink($uploadTmp);
        }
    }
}
