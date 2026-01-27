<?php
/**
 * Activity Log CSV Exporter
 * Exports filtered activity logs to CSV format
 */

// Verify admin access
require_once __DIR__ . '/../../../includes/middleware/secure-admin.php';

// Database connection
require_once __DIR__ . '/../../../includes/db/db-connection.php';

// Get filter parameters from request
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;
$activityType = $_GET['activity_type'] ?? null;
$clientId = isset($_GET['client_id']) ? (int)$_GET['client_id'] : null;

// Base query
$query = "SELECT * FROM client_activities WHERE 1=1";
$params = [];

// Add filters (same as logs.php)
if ($startDate) {
    $query .= " AND created_at >= ?";
    $params[] = $startDate;
}
if ($endDate) {
    $query .= " AND created_at <= ?";
    $params[] = $endDate;
}
if ($activityType) {
    $query .= " AND activity_type = ?";
    $params[] = $activityType;
}
if ($clientId) {
    $query .= " AND client_id = ?";
    $params[] = $clientId;
}

// No pagination for export - get all matching records
$query .= " ORDER BY created_at DESC";

// Execute query
$stmt = $db->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set CSV headers
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="activity_logs_' . date('Y-m-d') . '.csv"');

// Open output stream
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Timestamp', 'Client ID', 'Action Type', 'Details']);

// Write data rows
foreach ($logs as $log) {
    fputcsv($output, [
        $log['created_at'],
        $log['client_id'],
        $log['activity_type'],
        $log['details']
    ]);
}

fclose($output);
exit;
