<?php
namespace CMS\Reports;

class ReportDataGenerator {
    public static function generate(string $reportType): array {
        $data = [];
        $headers = [];
        
        switch($reportType) {
            case 'user_activity':
                $headers = ['User ID', 'Name', 'Last Login', 'Actions'];
                // TODO: Replace with actual data source
                $data = [
                    [1, 'Admin', '2025-05-25', 42],
                    [2, 'Editor', '2025-05-24', 15]
                ];
                break;
                
            case 'content_stats':
                $headers = ['Content Type', 'Published', 'Drafts', 'Total'];
                // TODO: Replace with actual data source
                $data = [
                    ['Articles', 12, 3, 15],
                    ['Pages', 8, 1, 9]
                ];
                break;
                
            default:
                throw new \InvalidArgumentException("Invalid report type: $reportType");
        }
        
        return [
            'headers' => $headers,
            'data' => $data
        ];
    }
}
