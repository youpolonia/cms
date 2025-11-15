<?php
// UMB: Added CSV export support and download endpoint
class ReportsController {
    protected $reportTypes = [
        'user_activity' => 'User Activity Report',
        'content_views' => 'Content Views Report',
        'system_usage' => 'System Usage Report',
        'client_activity' => 'Client Activity Dashboard'
    ];

    public function getReportTypes() {
        return $this->reportTypes;
    }

    public function index() {
        $title = 'Reports';
        $content = '<h2>Available Reports</h2>';
        $content .= '<div class="report-list">';
        
        foreach ($this->reportTypes as $id => $name) {
            $content .= "<div class='report-item'>
                <h3>{$name}</h3>
                <a href='/admin/reports/generate.php?type={$id}' class='btn'>Generate</a>
                <a href='/admin/reports/export.php?type={$id}' class='btn'>Export PDF</a>
                <a href='/admin/reports/schedule.php?type={$id}' class='btn'>Schedule</a>
            </div>";
        }

        $content .= '</div>';

        return [
            'title' => $title,
            'content' => $content
        ];
    }

    public function generate($reportType, $filters = []) {
        if (!array_key_exists($reportType, $this->reportTypes)) {
            return ['status' => 'error', 'message' => 'Invalid report type'];
        }

        $data = $this->getReportData($reportType, $filters);
        return [
            'status' => 'success',
            'data' => $data,
            'report_type' => $reportType
        ];
    }

    private function getReportData($reportType, $filters) {
        if ($reportType === 'client_activity') {
            require_once __DIR__ . '/../../models/ClientActivity.php';
            $clientActivity = new ClientActivity();

            $activities = $clientActivity->getActivities($filters);

            return [
                'headers' => ['Client', 'Activity Type', 'Details', 'Timestamp'],
                'rows' => array_map(function($activity) {
                    return [
                        $activity['client_name'],
                        $activity['activity_type'],
                        json_decode($activity['activity_details'], true),
                        $activity['created_at']
                    ];
                }, $activities)
            ];
        }

        // Default implementation for other report types
        return [
            'headers' => ['Date', 'Count'],
            'rows' => [
                ['2023-01-01', 42],
                ['2023-01-02', 37]
            ]
        ];
    }

    public function export($reportId, $format = 'pdf') {
        $data = $this->generate($reportId);
        if ($data['status'] !== 'success') {
            return $data;
        }

        if ($format === 'csv') {
            require_once __DIR__ . '/../../includes/export/csvexporter.php';
            $exporter = new CsvExporter();
            $result = $exporter->generate($data['data'], 'report_' . $reportId);

            if ($result['status'] === 'success') {
                return [
                    'status' => 'success',
                    'format' => 'csv',
                    'download_url' => $result['download_url']
                ];
            }
            return $result;
        }

        // Default to PDF (to be implemented)
        require_once __DIR__ . '/../core/tmp_sandbox.php';
        return [
            'status' => 'success',
            'file' => cms_tmp_path('report_'.$reportId.'.pdf'),
            'message' => 'PDF export not yet implemented'
        ];
    }

    public function schedule($reportId, $scheduleOptions) {
        // TODO: Implement scheduling logic
        return [
            'status' => 'success',
            'schedule_id' => uniqid(),
            'message' => 'Scheduling not yet implemented'
        ];
    }
}
