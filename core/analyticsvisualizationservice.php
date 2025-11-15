<?php
declare(strict_types=1);

class AnalyticsVisualizationService {
    private const CHART_TYPES = ['line', 'bar', 'pie', 'table'];
    private const EXPORT_FORMATS = ['pdf', 'csv', 'json'];
    
    /**
     * Generate chart data from aggregated analytics
     */
    public static function generateChart(
        array $aggregatedData, 
        string $chartType,
        ?string $title = null
    ): array {
        if (!in_array($chartType, self::CHART_TYPES)) {
            throw new InvalidArgumentException("Invalid chart type: $chartType");
        }

        return [
            'type' => $chartType,
            'title' => $title ?? 'Analytics Dashboard',
            'data' => $aggregatedData,
            'timestamp' => time()
        ];
    }

    /**
     * Export analytics data in specified format
     */
    public static function exportData(
        array $data, 
        string $format,
        ?string $filename = null
    ): string {
        if (!in_array($format, self::EXPORT_FORMATS)) {
            throw new InvalidArgumentException("Invalid export format: $format");
        }

        $filename = $filename ?? 'analytics_export_' . date('Ymd_His');
        
        switch ($format) {
            case 'pdf':
                return self::generatePdf($data, $filename);
            case 'csv':
                return self::generateCsv($data, $filename);
            case 'json':
                return json_encode($data);
            default:
                throw new RuntimeException("Unsupported export format");
        }
    }

    private static function generatePdf(array $data, string $filename): string {
        // PDF generation logic would go here
        return "PDF content for $filename";
    }

    private static function generateCsv(array $data, string $filename): string {
        // CSV generation logic would go here
        return "CSV content for $filename";
    }

    /**
     * Get available visualization options
     */
    public static function getVisualizationOptions(): array {
        return [
            'chart_types' => self::CHART_TYPES,
            'export_formats' => self::EXPORT_FORMATS
        ];
    }
}
