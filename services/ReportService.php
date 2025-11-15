<?php
/**
 * Report Generation Service
 */
class ReportService {
    private $db;
    private $storagePath;

    public function __construct() {
        require_once __DIR__ . '/../core/database.php';
        $this->db = \core\Database::connection();
        $this->storagePath = __DIR__ . '/../storage/reports/';
        
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function generate(string $type = 'pdf'): string {
        $reportData = $this->getReportData();
        $filename = 'report_' . date('Y-m-d_H-i-s') . '.' . $type;
        $filepath = $this->storagePath . $filename;

        switch ($type) {
            case 'pdf':
                $this->generatePdf($reportData, $filepath);
                break;
            case 'csv':
                $this->generateCsv($reportData, $filepath);
                break;
            default:
                throw new Exception("Unsupported report type: $type");
        }

        return '/storage/reports/' . $filename;
    }

    private function getReportData(): array {
        $query = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as events,
                    COUNT(DISTINCT user_id) as users
                  FROM analytics_events
                  GROUP BY DATE(created_at)
                  ORDER BY date DESC
                  LIMIT 30";
        
        $result = $this->db->query($query);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    private function generatePdf(array $data, string $filepath): void {
        // PDF generation implementation would go here
        // Using a PDF library like TCPDF or similar
        file_put_contents($filepath, "PDF Report Content");
    }

    private function generateCsv(array $data, string $filepath): void {
        $fp = fopen($filepath, 'w');
        fputcsv($fp, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        fclose($fp);
    }
}
