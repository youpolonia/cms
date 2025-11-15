<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="worker_metrics_export_' . date('Y-m-d') . '.csv"');

// Connect to database
require_once __DIR__ . '/../../../core/database.php';
$pdo = \core\Database::connection();

// Get date range from request
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Prepare and execute query
$stmt = $pdo->prepare("
    SELECT 
        w.id,
        w.name,
        w.status,
        COUNT(a.id) AS activity_count,
        COUNT(n.id) AS notification_count,
        AVG(a.execution_time) AS avg_execution_time
    FROM workers w
    LEFT JOIN worker_activity_logs a ON w.id = a.worker_id AND a.created_at BETWEEN :start_date AND :end_date
    LEFT JOIN worker_notifications n ON w.id = n.worker_id AND n.created_at BETWEEN :start_date AND :end_date
    GROUP BY w.id
");
$stmt->execute(['start_date' => $startDate, 'end_date' => $endDate]);

// Output CSV
$output = fopen('php://output', 'w');

// Write headers
fputcsv($output, [
    'Worker ID',
    'Name', 
    'Status',
    'Activity Count',
    'Notification Count',
    'Avg Execution Time (ms)'
]);

// Write data
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, $row);
}

fclose($output);
