<?php

class ReportScheduler {
    private $db;
    private $exporter;
    private $mailer;
    private $reportsController;

    public function __construct(PDO $db, Mailer $mailer, ReportsController $reportsController) {
        $this->db = $db;
        $this->mailer = $mailer;
        $this->reportsController = $reportsController;
        
        if (!class_exists('CsvExporter')) {
            require_once __DIR__ . '/../export/csvexporter.php';
        }
        if (!class_exists('ExcelExporter')) {
            require_once __DIR__ . '/../export/excelexporter.php';
        }
    }

    private function calculateNextRun(string $schedule, ?string $customSchedule): string {
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $nextRun = clone $now;

        switch ($schedule) {
            case 'daily':
                $nextRun->add(new DateInterval('P1D'));
                break;
            case 'weekly':
                $nextRun->add(new DateInterval('P7D'));
                break;
            case 'monthly':
                $nextRun->add(new DateInterval('P1M'));
                break;
            case 'custom':
                if (!$customSchedule) {
                    throw new InvalidArgumentException('Custom schedule requires custom_schedule parameter');
                }
                try {
                    $interval = new DateInterval($customSchedule);
                    $nextRun->add($interval);
                } catch (Exception $e) {
                    throw new InvalidArgumentException('Invalid custom schedule format: ' . $e->getMessage());
                }
                break;
            default:
                throw new InvalidArgumentException("Invalid schedule type: $schedule");
        }

        // Set time to midnight UTC
        $nextRun->setTime(0, 0, 0);
        return $nextRun->format('Y-m-d H:i:s');
    }

    private function updateNextRun(int $reportId): void {
        $stmt = $this->db->prepare("
            UPDATE scheduled_reports 
            SET last_run = ?, 
                next_run = ? 
            WHERE id = ?
        ");

        $now = date('Y-m-d H:i:s');
        $nextRun = $this->calculateNextRun(
            $this->getReportSchedule($reportId)['schedule'],
            $this->getReportSchedule($reportId)['custom_schedule']
        );

        $stmt->execute([$now, $nextRun, $reportId]);
    }

    private function getReportSchedule(int $reportId): array {
        $stmt = $this->db->prepare("
            SELECT schedule, custom_schedule 
            FROM scheduled_reports 
            WHERE id = ?
        ");
        $stmt->execute([$reportId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ... [rest of the existing methods remain unchanged] ...

    public function processWebhook(array $payload): array {
        // Validate required fields
        $required = ['event_type', 'report_id', 'schedule_time'];
        foreach ($required as $field) {
            if (!isset($payload[$field])) {
                throw new InvalidArgumentException("Missing required field: $field");
            }
        }

        // Queue report based on schedule_time
        try {
            $this->updateNextRun($payload['report_id']);
            
            return [
                'status' => 'success',
                'message' => 'Report queued successfully',
                'next_run' => $this->getReportSchedule($payload['report_id'])['next_run']
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
