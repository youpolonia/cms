<?php
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');

class BackupManager {
    private string $backupDir;

    public function __construct(string $backupDir) {
        $this->backupDir = rtrim($backupDir, '/') . '/';
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    public function exportSettings(): string|false {
        try {
            $settings = [
                'system' => $this->getSystemSettings(),
                'content_types' => $this->getContentTypes(),
                'users' => $this->getUserSettings()
            ];
            
            $filename = $this->backupDir . 'settings_' . date('Ymd_His') . '.json';
            file_put_contents($filename, json_encode($settings, JSON_PRETTY_PRINT));
            return $filename;
        } catch (Exception $e) {
            error_log("BackupManager exportSettings failed: " . $e->getMessage());
            return false;
        }
    }

    public function exportContent(): string|false {
        try {
            $content = [
                'nodes' => $this->getAllContentNodes(),
                'media' => $this->getMediaReferences()
            ];
            
            $filename = $this->backupDir . 'content_' . date('Ymd_His') . '.json';
            file_put_contents($filename, json_encode($content, JSON_PRETTY_PRINT));
            return $filename;
        } catch (Exception $e) {
            error_log("BackupManager exportContent failed: " . $e->getMessage());
            return false;
        }
    }

    public function generateTimestampedBackup(): string|false {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $settingsFile = $this->exportSettings();
        $contentFile = $this->exportContent();
        
        if (!$settingsFile || !$contentFile) {
            // Clean up any partial files
            if ($settingsFile) unlink($settingsFile);
            if ($contentFile) unlink($contentFile);
            return false;
        }

        $zipFile = $this->backupDir . 'backup_' . date('Ymd_His') . '.zip';
        if ($this->createZip([$settingsFile, $contentFile], $zipFile)) {
            // Clean up temp files
            unlink($settingsFile);
            unlink($contentFile);
            return $zipFile;
        }
        
        return false;
    }

    private function createZip(array $files, string $zipPath): bool {
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return false;
        }

        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }

        return $zip->close();
    }

    // Helper methods would be implemented here
    private function getSystemSettings(): array { /* ... */ }
    private function getContentTypes(): array { /* ... */ }
    private function getUserSettings(): array { /* ... */ }
    private function getAllContentNodes(): array { /* ... */ }
    private function getMediaReferences(): array { /* ... */ }
}
