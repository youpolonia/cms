<?php
/**
 * Task to refresh tenant cache
 */
class RefreshCacheTask {
    private $tenantId;
    private $logger;

    public function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->logger = new TaskLogger();
    }

    public function execute() {
        $this->logger->log("Starting RefreshCacheTask for tenant {$this->tenantId}");
        
        try {
            $cacheDir = "cache/tenant_{$this->tenantId}";
            $filesCleared = 0;
            
            if (is_dir($cacheDir)) {
                // Clear all .cache files
                foreach (glob("$cacheDir/*.cache") as $file) {
                    unlink($file);
                    $filesCleared++;
                }
                
                // Clear compiled templates
                $templatesDir = "$cacheDir/templates";
                if (is_dir($templatesDir)) {
                    array_map('unlink', glob("$templatesDir/*.php"));
                }
            }
            
            $this->logger->log("Completed RefreshCacheTask. Cleared {$filesCleared} cache files");
            return true;
        } catch (Exception $e) {
            $this->logger->log("RefreshCacheTask failed: " . $e->getMessage());
            return false;
        }
    }
}
