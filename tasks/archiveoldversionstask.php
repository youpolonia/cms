<?php
/**
 * Task to archive old content versions
 */
class ArchiveOldVersionsTask {
    private $tenantId;
    private $logger;

    public function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->logger = new TaskLogger();
    }

    public function execute() {
        $this->logger->log("Starting ArchiveOldVersionsTask for tenant {$this->tenantId}");
        
        try {
            $versionsDir = "content/tenant_{$this->tenantId}/versions";
            $archiveDir = "content/tenant_{$this->tenantId}/archives";
            $versionsToKeep = 5;
            $filesArchived = 0;
            
            if (!is_dir($archiveDir)) {
                mkdir($archiveDir, 0755, true);
            }

            if (is_dir($versionsDir)) {
                $files = glob("$versionsDir/*.json");
                if (count($files) > $versionsToKeep) {
                    usort($files, function($a, $b) {
                        return filemtime($a) - filemtime($b);
                    });
                    
                    $filesToArchive = array_slice($files, 0, -$versionsToKeep);
                    foreach ($filesToArchive as $file) {
                        $archiveFile = $archiveDir . '/' . basename($file);
                        rename($file, $archiveFile);
                        $filesArchived++;
                    }
                }
            }
            
            $this->logger->log("Completed ArchiveOldVersionsTask. Archived {$filesArchived} old versions");
            return true;
        } catch (Exception $e) {
            $this->logger->log("ArchiveOldVersionsTask failed: " . $e->getMessage());
            return false;
        }
    }
}
