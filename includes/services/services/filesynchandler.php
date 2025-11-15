<?php
/**
 * Handles atomic file synchronization with rollback capability
 */
class FileSyncHandler {
    private $backupDir = __DIR__ . '/../../storage/deployment_backups/';
    private $syncLog = [];
    private $stateFile = __DIR__ . '/../../storage/deployment_state.json';
    private $conflictStrategy = 'timestamp'; // Options: timestamp, version, manual
    
    public function __construct() {
        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        $this->loadState();
    }
    
    /**
     * Synchronize files between source and target with incremental updates
     */
    public function sync(string $source, string $target): array {
        $sourcePath = $this->getEnvironmentPath($source);
        $targetPath = $this->getEnvironmentPath($target);
        
        if (!file_exists($sourcePath)) {
            throw new Exception("Source environment $source not found");
        }
        
        // Start atomic operation
        $transactionId = uniqid('sync_');
        $this->syncLog[$transactionId] = [
            'started_at' => microtime(true),
            'files' => []
        ];
        
        $files = $this->scanFiles($sourcePath);
        $results = [];
        $lastSync = $this->getLastSyncTime($source, $target);
        
        foreach ($files as $file) {
            $relativePath = substr($file, strlen($sourcePath));
            $targetFile = $targetPath . $relativePath;
            
            // Skip if unchanged since last sync
            $fileMtime = filemtime($file);
            if ($lastSync && $fileMtime <= $lastSync) {
                continue;
            }
            
            // Handle conflicts
            if (file_exists($targetFile)) {
                $conflictResolved = $this->resolveConflict($file, $targetFile);
                if (!$conflictResolved) {
                    continue;
                }
            }
            
            // Atomic file operation with backup
            $backupFile = $this->backupDir . $transactionId . '_' . basename($targetFile) . '.bak';
            if (file_exists($targetFile)) {
                copy($targetFile, $backupFile);
            }
            
            $tempFile = $targetFile . '.tmp';
            copy($file, $tempFile);
            rename($tempFile, $targetFile);
            
            $this->syncLog[$transactionId]['files'][$targetFile] = $backupFile;
            $results[] = $relativePath;
        }
        
        // Update sync state
        $this->updateSyncState($source, $target);
        
        return [
            'status' => 'success',
            'transaction_id' => $transactionId,
            'files_updated' => count($results),
            'details' => $results
        ];
    }
    
    /**
     * Rollback file changes for a specific transaction
     */
    public function rollback(string $transactionId = null): array {
        $results = [];
        $transactions = $transactionId ? [$transactionId => $this->syncLog[$transactionId]] : $this->syncLog;
        
        foreach ($transactions as $txId => $txData) {
            foreach ($txData['files'] as $targetFile => $backupFile) {
                if (file_exists($backupFile)) {
                    copy($backupFile, $targetFile);
                    unlink($backupFile);
                    $results[] = $targetFile;
                }
            }
            unset($this->syncLog[$txId]);
        }
        
        return [
            'status' => 'success',
            'files_restored' => count($results),
            'details' => $results
        ];
    }
    
    private function resolveConflict(string $sourceFile, string $targetFile): bool {
        switch ($this->conflictStrategy) {
            case 'timestamp':
                return filemtime($sourceFile) > filemtime($targetFile);
            case 'version':
                // Version-based conflict resolution would go here
                return true;
            case 'manual':
                // Manual conflict resolution would go here  
                return false;
            default:
                return false;
        }
    }
    
    private function loadState(): void {
        if (file_exists($this->stateFile)) {
            $this->state = json_decode(file_get_contents($this->stateFile), true);
        } else {
            $this->state = [];
        }
    }
    
    private function updateSyncState(string $source, string $target): void {
        $this->state["{$source}_{$target}"] = time();
        file_put_contents($this->stateFile, json_encode($this->state));
    }
    
    private function getLastSyncTime(string $source, string $target): ?int {
        return $this->state["{$source}_{$target}"] ?? null;
    }
    
    private function getEnvironmentPath(string $env): string {
        return __DIR__ . "/../../environments/$env/";
    }
    
    private function scanFiles(string $path): array {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $files[] = $file->getPathname();
            }
        }
        
        return $files;
    }
}
