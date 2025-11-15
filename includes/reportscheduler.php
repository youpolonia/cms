<?php
class ReportScheduler {
    private $db;
    private $reportsController;

    public function __construct($db, $reportsController) {
        $this->db = $db;
        $this->reportsController = $reportsController;
    }

    public function processWebhook($data) {
        if (empty($data['report_type'])) {
            throw new Exception('Missing report type', 400);
        }

        $result = $this->reportsController->schedule(
            $data['report_type'],
            [
                'frequency' => $data['frequency'] ?? 'daily',
                'recipients' => $data['recipients'] ?? [],
                'format' => $data['format'] ?? 'pdf'
            ]
        );

        if ($result['status'] !== 'success') {
            throw new Exception('Failed to schedule report: ' . $result['message'], 500);
        }

        return $result;
    }
}
