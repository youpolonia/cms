<?php
require_once __DIR__.'/../core/contentscheduler.php';
require_once __DIR__.'/../core/logging.php';

header('Content-Type: text/plain');

try {
    log_message('Starting scheduled content publishing job');
    $startTime = microtime(true);
    
    ContentScheduler::checkPendingContent();
    
    $duration = round(microtime(true) - $startTime, 3);
    log_message("Completed publishing job in {$duration}s");
    echo "Successfully processed scheduled content\n";
} catch (Exception $e) {
    log_message("Publishing job failed: " . $e->getMessage(), 'error');
    http_response_code(500);
    echo "Error processing scheduled content: " . $e->getMessage() . "\n";
}
