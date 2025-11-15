<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) {
    http_response_code(403);
    exit('Access denied');
}
/**
 * Memory Bank Browser
 * Provides secure navigation, search and viewing for memory-bank files
 */
class MemoryBrowser {
    private string $memoryDir;
    private $security;
    private array $allowedExtensions = ['md', 'txt', 'php', 'json', 'log'];

    public function __construct() {
        $this->memoryDir = realpath(__DIR__ . '/../../memory-bank/') . '/';
        require_once __DIR__ . '/../securitymiddleware.php';
        $this->security = new DeveloperToolsSecurity();
    }

    public function listFiles(): array {
        if (!$this->security->checkAccess()) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->memoryDir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $this->isValidFile($file->getPathname())) {
                $files[] = str_replace($this->memoryDir, '', $file->getPathname());
            }
        }

        return $files;
    }

    public function getFileContent(string $path): string {
        if (!$this->isValidPath($path)) {
            return '';
        }

        $fullPath = $this->memoryDir . $path;
        if (!file_exists($fullPath)) {
            return '';
        }

        return $this->security->sanitizeOutput(file_get_contents($fullPath));
    }

    public function searchFiles(string $query): array {
        if (!$this->security->checkAccess()) {
            return [];
        }

        $results = [];
        foreach ($this->listFiles() as $file) {
            $content = $this->getFileContent($file);
            if (stripos($content, $query) !== false) {
                $results[] = [
                    'file' => $file,
                    'matches' => substr_count(strtolower($content), strtolower($query))
                ];
            }
        }

        return $results;
    }

    public function exportFile(string $path): void {
        if (!$this->isValidPath($path)) {
            return;
        }

        $fullPath = $this->memoryDir . $path;
        if (file_exists($fullPath)) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            readfile($fullPath);
            exit;
        }
    }

    private function isValidPath(string $path): bool {
        // Prevent directory traversal
        if (strpos($path, '../') !== false || strpos($path, '..\\') !== false) {
            return false;
        }

        $fullPath = realpath($this->memoryDir . $path);
        return $fullPath && strpos($fullPath, $this->memoryDir) === 0;
    }

    private function isValidFile(string $path): bool {
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        return in_array(strtolower($ext), $this->allowedExtensions);
    }
}
