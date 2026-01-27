<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }

/**
 * Phase 12 Real-Time Analytics Reporter
 * Handles real-time data streaming and SSE (Server-Sent Events)
 */
class RealTimeReporter {
    private \PDO $pdo;
    private int $updateInterval = 1; // seconds
    private bool $active = false;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function setUpdateInterval(int $seconds): void {
        $this->updateInterval = max(1, $seconds);
    }

    public function startStreaming(): void {
        if ($this->active) {
            return;
        }

        $this->active = true;
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        while ($this->active) {
            $data = $this->getLatestMetrics();
            $this->sendEvent('update', $data);
            
            if (connection_aborted()) {
                $this->active = false;
                break;
            }

            sleep($this->updateInterval);
        }
    }

    private function getLatestMetrics(): array {
        $stmt = $this->pdo->query(
            "SELECT 
                COUNT(*) as total_metrics,
                SUM(CASE WHEN processing_status = 'completed' THEN 1 ELSE 0 END) as processed,
                SUM(CASE WHEN processing_status = 'pending' THEN 1 ELSE 0 END) as pending
             FROM analytics_metrics"
        );
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function sendEvent(string $event, array $data): void {
        echo "event: $event\n";
        echo "data: " . json_encode($data) . "\n\n";
        ob_flush();
        flush();
    }

    public static function test(\PDO $pdo): bool {
        try {
            $reporter = new self($pdo);
            $metrics = $reporter->getLatestMetrics();
            return is_array($metrics) && isset($metrics['total_metrics']);
        } catch (\Exception $e) {
            error_log("Reporter test failed: " . $e->getMessage());
            return false;
        }
    }
}
