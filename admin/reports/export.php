<?php
require_once __DIR__ . '/../../includes/export/excelexporter.php';
require_once __DIR__.'/../../includes/reports/reportdatagenerator.php';

try {
    // Validate report type parameter
    if (!isset($_GET['type']) || !in_array($_GET['type'], ['summary', 'detailed'])) {
        throw new InvalidArgumentException('Invalid report type specified');
    }

    // Generate report data
    $reportData = CMS\Reports\ReportDataGenerator::generate($_GET['type']);
    $headers = $reportData['headers'];
    $data = $reportData['rows'];

    // Generate Excel content
    $excelContent = CMS\Export\ExcelExporter::export($data, $headers);

    // Set headers for download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="report_export_'.date('Y-m-d').'.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output the content
    echo $excelContent;
} catch (\Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log($e->getMessage());
    echo json_encode(['error' => 'Internal error']);
}
