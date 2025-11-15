<?php
/**
 * Handles analytics dashboard requests
 */
class AnalyticsDashboardController {
    private $reportGenerator;

    public function __construct(ReportGenerator $reportGenerator) {
        $this->reportGenerator = $reportGenerator;
    }

    public function getDashboardData(): array {
        $today = new DateTime();
        $yesterday = (clone $today)->modify('-1 day');
        $lastWeek = (clone $today)->modify('-7 days');

        return [
            'today' => $this->reportGenerator->generateDailyReport($today),
            'yesterday' => $this->reportGenerator->generateDailyReport($yesterday),
            'last_week' => $this->reportGenerator->generateDailyReport($lastWeek),
            'trends' => $this->getWeeklyTrends()
        ];
    }

    private function getWeeklyTrends(): array {
        $trends = [];
        $today = new DateTime();
        
        for ($i = 0; $i < 7; $i++) {
            $date = (clone $today)->modify("-$i days");
            $report = $this->reportGenerator->generateDailyReport($date);
            $trends[] = [
                'date' => $date->format('Y-m-d'),
                'page_views' => $report['page_views'],
                'unique_visitors' => $report['unique_visitors']
            ];
        }

        return array_reverse($trends);
    }

    public function getPerformanceMetrics(): array {
        $today = new DateTime();
        $report = $this->reportGenerator->generateDailyReport($today);
        return $report['performance_metrics'];
    }

    public function getEngagementStats(): array {
        $today = new DateTime();
        $report = $this->reportGenerator->generateDailyReport($today);
        return $report['engagement_stats'];
    }
}
