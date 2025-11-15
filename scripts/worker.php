<?php
/**
 * Worker Process Script
 * Handles job processing in background
 */

require_once __DIR__ . '/../includes/services/workerprocessmanager.php';
require_once __DIR__ . '/../includes/services/jobprocessor.php';

// Get worker type from CLI args
$workerType = $argv[1] ?? 'default';

try {
    $manager = new WorkerProcessManager();
    $processor = new JobProcessor();
    
    // Register worker
    $workerId = $manager->startWorker($workerType);
    
    // Main worker loop
    while (true) {
        try {
            // Get next job
            if ($job = $manager->getNextJob($workerId)) {
                // Process job
                $result = $processor->process($job);
                $manager->completeJob($job['id'], $result);
            } else {
                // No jobs available, sleep briefly
                sleep(5);
            }
            
            // Send heartbeat
            $manager->sendHeartbeat($workerId);
        } catch (Exception $e) {
            if (isset($job)) {
                $manager->failJob($job['id'], $e->getMessage());
            }
            error_log("Worker error: " . $e->getMessage());
            sleep(10); // Wait before retrying
        }
    }
} catch (Exception $e) {
    error_log("Fatal worker error: " . $e->getMessage());
    exit(1);
}
