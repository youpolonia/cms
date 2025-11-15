<?php
declare(strict_types=1);

/**
 * Performance - Job Queue
 * Handles background job processing
 */
class JobQueue {
    private static string $logFile = __DIR__ . '/../../logs/job_queue.log';
    private static int $maxRetries = 3;
    private static int $retryDelay = 60; // seconds

    /**
     * Add a job to the queue
     */
    public static function addJob(
        string $jobType,
        array $payload,
        int $priority = 0,
        ?string $scheduledAt = null
    ): string {
        $jobId = uniqid('job_', true);
        $jobData = [
            'id' => $jobId,
            'type' => $jobType,
            'payload' => $payload,
            'status' => 'pending',
            'priority' => $priority,
            'created_at' => time(),
            'scheduled_at' => $scheduledAt ? strtotime($scheduledAt) : time(),
            'attempts' => 0
        ];

        self::storeJob($jobData);
        self::logEvent("Job queued: $jobId ($jobType)");
        
        return $jobId;
    }

    /**
     * Process next available job
     */
    public static function processNextJob(): bool {
        $job = self::getNextJob();
        if (!$job) {
            return false;
        }

        try {
            self::updateJobStatus($job['id'], 'processing');
            $handler = self::getJobHandler($job['type']);
            $handler->process($job['payload']);
            self::updateJobStatus($job['id'], 'completed');
            self::logEvent("Job completed: {$job['id']}");
            return true;
        } catch (Exception $e) {
            self::handleJobFailure($job, $e);
            return false;
        }
    }

    private static function getNextJob(): ?array {
        // Get highest priority pending job that's ready to run
        $jobs = self::getPendingJobs();
        foreach ($jobs as $job) {
            if ($job['scheduled_at'] <= time()) {
                return $job;
            }
        }
        return null;
    }

    private static function handleJobFailure(array $job, Exception $e): void {
        $attempts = $job['attempts'] + 1;
        $status = ($attempts >= self::$maxRetries) ? 'failed' : 'retrying';
        
        self::updateJob($job['id'], [
            'status' => $status,
            'attempts' => $attempts,
            'last_error' => $e->getMessage(),
            'next_attempt' => time() + self::$retryDelay
        ]);

        self::logEvent("Job failed: {$job['id']} (Attempt $attempts) - {$e->getMessage()}");
    }

    private static function getJobHandler(string $type): object {
        $handlerClass = ucfirst($type) . 'JobHandler';
        if (!class_exists($handlerClass)) {
            throw new RuntimeException("No handler for job type: $type");
        }
        return new $handlerClass();
    }

    private static function storeJob(array $jobData): void {
        // Implementation would save to database
        file_put_contents(
            __DIR__ . '/../../storage/jobs/' . $jobData['id'] . '.json',
            json_encode($jobData)
        );
    }

    private static function updateJobStatus(string $jobId, string $status): void {
        self::updateJob($jobId, ['status' => $status]);
    }

    private static function updateJob(string $jobId, array $updates): void {
        $file = __DIR__ . '/../../storage/jobs/' . $jobId . '.json';
        if (file_exists($file)) {
            $job = json_decode(file_get_contents($file), true);
            $job = array_merge($job, $updates);
            file_put_contents($file, json_encode($job));
        }
    }

    private static function getPendingJobs(): array {
        // Implementation would query database
        $jobs = [];
        $files = glob(__DIR__ . '/../../storage/jobs/*.json');
        foreach ($files as $file) {
            $job = json_decode(file_get_contents($file), true);
            if ($job['status'] === 'pending' || $job['status'] === 'retrying') {
                $jobs[] = $job;
            }
        }
        
        // Sort by priority (highest first) then creation time (oldest first)
        usort($jobs, function($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return $a['created_at'] <=> $b['created_at'];
            }
            return $b['priority'] <=> $a['priority'];
        });

        return $jobs;
    }

    private static function logEvent(string $message): void {
        file_put_contents(
            self::$logFile,
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }

    // BREAKPOINT: Continue with worker management methods
}
