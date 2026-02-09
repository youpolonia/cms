<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use Core\Request;
use Core\Response;
use Core\Session;

class AnalyticsController
{
    private ?\AnalyticsService $service = null;

    public function __construct()
    {
        if (file_exists(CMS_ROOT . '/services/analyticsservice.php')) {
            require_once CMS_ROOT . '/services/analyticsservice.php';
            $pdo = db();
            $tenantId = Session::get('tenant_id');
            $this->service = new \AnalyticsService($pdo, $tenantId ? (int)$tenantId : null);
        }
    }

    public function index(Request $request): void
    {
        $period = $request->get('period', '7d');
        $validPeriods = ['24h', '7d', '30d', '90d', 'year'];

        if (!in_array($period, $validPeriods, true)) {
            $period = '7d';
        }

        $data = $this->service ? $this->service->getDashboardData($period) : ['pageviews'=>[],'top_pages'=>[],'summary'=>['total_views'=>0,'unique_visitors'=>0,'total_sessions'=>0,'avg_duration'=>0]];

        render('admin/analytics/index', [
            'period' => $period,
            'validPeriods' => $validPeriods,
            'data' => $data,
            'success' => Session::getFlash('success'),
            'error' => Session::getFlash('error')
        ]);
    }

    public function realtime(Request $request): void
    {
        header('Content-Type: application/json');

        try {
            $stats = $this->service ? $this->service->getRealTimeStats() : [];
            echo json_encode(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to fetch stats']);
        }
        exit;
    }

    public function export(Request $request): void
    {
        $startDate = $request->get('start_date', date('Y-m-d', strtotime('-30 days')));
        $endDate = $request->get('end_date', date('Y-m-d'));

        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $startDate)) {
            $startDate = date('Y-m-d', strtotime('-30 days'));
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $endDate)) {
            $endDate = date('Y-m-d');
        }

        $report = $this->service ? $this->service->generateReport($startDate, $endDate) : ['summary'=>['total_views'=>0,'unique_visitors'=>0,'total_sessions'=>0,'avg_duration'=>0],'top_pages'=>[]];

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="analytics_' . $startDate . '_' . $endDate . '.csv"');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Analytics Report - ' . $startDate . ' to ' . $endDate]);
        fputcsv($out, []);
        fputcsv($out, ['Metric', 'Value']);
        fputcsv($out, ['Total Views', $report['summary']['total_views'] ?? 0]);
        fputcsv($out, ['Unique Visitors', $report['summary']['unique_visitors'] ?? 0]);
        fputcsv($out, ['Sessions', $report['summary']['total_sessions'] ?? 0]);
        fputcsv($out, ['Avg Duration', $report['summary']['avg_duration'] ?? 0]);
        fputcsv($out, []);
        fputcsv($out, ['Top Pages']);
        fputcsv($out, ['URL', 'Views']);
        foreach (($report['top_pages'] ?? []) as $page) {
            fputcsv($out, [$page['page_url'] ?? '', $page['view_count'] ?? 0]);
        }
        fclose($out);
        exit;
    }
}
