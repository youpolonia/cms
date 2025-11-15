<?php
declare(strict_types=1);

/**
 * Content Archival Scheduler - Phase 14 Implementation
 * Handles scheduled execution of content archival
 */
class ContentArchivalScheduler {
    private ContentArchivalSystem $archivalSystem;
    private Logger $logger;

    public function __construct(
        ContentArchivalSystem $archivalSystem,
        Logger $logger
    ) {
        $this->archivalSystem = $archivalSystem;
        $this->logger = $logger;
    }

    /**
     * Run daily archival process
     */
    public function runDailyArchival(): void {
        $this->logger->log('Starting daily content archival');
        
        try {
            $archivedCount = $this->archivalSystem->archiveExpiredContent();
            $this->logger->log("Daily archival completed. Archived $archivedCount items");
        } catch (\Exception $e) {
            $this->logger->error("Daily archival failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Run weekly deep archival (more thorough checks)
     */
    public function runWeeklyArchival(): void {
        $this->logger->log('Starting weekly deep archival');
        // TODO: Implement more comprehensive archival checks
    }
}
