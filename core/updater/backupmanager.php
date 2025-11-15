<?php
namespace Core\Updater;

use Core\Updater\Exceptions\BackupException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class BackupManager
{
    private $backupDir;

    public function __construct()
    {
        $this->backupDir = ROOT_PATH . '/backups/updates/';
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * Creates a backup of specified directories
     */
    public function createBackup(array $directories): string
    {
        $backupId = date('Ymd_His') . '_' . uniqid();
        $backupPath = $this->backupDir . $backupId . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE) !== true) {
            throw new BackupException("Failed to create backup archive");
        }

        foreach ($directories as $dir) {
            $fullPath = ROOT_PATH . '/' . ltrim($dir, '/');
            if (!file_exists($fullPath)) {
                continue;
            }

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fullPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(ROOT_PATH) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        if ($zip->close() === false) {
            throw new BackupException("Failed to finalize backup archive");
        }

        return $backupId;
    }

    /**
     * Restores a backup by ID
     */
    public function restoreBackup(string $backupId): bool
    {
        $backupPath = $this->backupDir . $backupId . '.zip';
        if (!file_exists($backupPath)) {
            throw new BackupException("Backup not found");
        }

        $zip = new ZipArchive();
        if ($zip->open($backupPath) !== true) {
            throw new BackupException("Failed to open backup archive");
        }

        $zip->extractTo(ROOT_PATH);
        $zip->close();

        return true;
    }

    /**
     * Lists all available backups
     */
    public function listBackups(): array
    {
        $backups = [];
        $files = glob($this->backupDir . '*.zip');
        
        foreach ($files as $file) {
            $backups[] = [
                'id' => basename($file, '.zip'),
                'date' => date('Y-m-d H:i:s', filemtime($file)),
                'size' => filesize($file)
            ];
        }

        return $backups;
    }
}
