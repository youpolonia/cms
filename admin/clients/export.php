<?php
require_once __DIR__ . '/../../includes/export/exportinterface.php';
require_once __DIR__ . '/../../includes/export/csvexporter.php';
require_once __DIR__ . '/../../includes/export/jsonexporter.php';
require_once __DIR__ . '/../../includes/export/xmlexporter.php';
// session boot (admin)
require_once __DIR__ . '/../../core/session_boot.php';

// Check permissions
cms_session_start('admin');
if (!isset($_SESSION['user_role'])) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

// Get export format from request
$format = strtolower($_GET['format'] ?? 'csv');
$allowedFormats = ['csv', 'json', 'xml'];

if (!in_array($format, $allowedFormats)) {
    header('HTTP/1.0 400 Bad Request');
    exit('Invalid export format');
}

// Get data (in a real app this would come from database)
$data = [
    ['id' => 1, 'name' => 'Client A', 'email' => 'a@example.com'],
    ['id' => 2, 'name' => 'Client B', 'email' => 'b@example.com'],
    ['id' => 3, 'name' => 'Client C', 'email' => 'c@example.com']
];

// Process export
try {
    switch ($format) {
        case 'csv':
            $exporter = CsvExporter::class;
            $contentType = 'text/csv';
            $extension = 'csv';
            break;
        case 'json':
            $exporter = JsonExporter::class;
            $contentType = 'application/json';
            $extension = 'json';
            break;
        case 'xml':
            $exporter = XmlExporter::class;
            $contentType = 'application/xml';
            $extension = 'xml';
            break;
    }

    if (!$exporter::checkExportPermission()) {
        header('HTTP/1.0 403 Forbidden');
        exit('Export permission denied');
    }

    $exportedData = $exporter::export($data);

    // Output headers and data
    header('Content-Type: ' . $contentType);
    header('Content-Disposition: attachment; filename="clients_' . date('Y-m-d') . '.' . $extension . '"');
    echo $exportedData;
    exit;

} catch (\Throwable $e) {
    header('HTTP/1.0 500 Internal Server Error');
    error_log($e->getMessage());
    exit('Export failed');
}
