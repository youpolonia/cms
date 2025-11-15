<?php
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/tenant_selector.php';

header('Content-Type: application/json');

// Verify admin access for current tenant
$tenantId = $_SESSION['current_tenant_id'] ?? null;
if (!has_admin_access($tenantId)) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Get query parameters
$range = $_GET['range'] ?? '7d';
$fromDate = $_GET['from'] ?? null;
$toDate = $_GET['to'] ?? null;

try {
    $pdo = \core\Database::connection();
    
    // Build date conditions based on range
    $dateConditions = buildDateConditions($range, $fromDate, $toDate);
    
    // Fetch visits data
    $visitsData = fetchVisitsData($pdo, $tenantId, $dateConditions);
    
    // Fetch content types data
    $contentData = fetchContentData($pdo, $tenantId, $dateConditions);
    
    // Fetch traffic sources data
    $sourcesData = fetchSourcesData($pdo, $tenantId, $dateConditions);
    
    // Fetch user engagement data
    $engagementData = fetchEngagementData($pdo, $tenantId, $dateConditions);
    
    // Return combined data
    echo json_encode([
        'visits' => formatVisitsData($visitsData),
        'contentTypes' => formatContentData($contentData),
        'trafficSources' => formatSourcesData($sourcesData),
        'userEngagement' => formatEngagementData($engagementData)
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}

function buildDateConditions($range, $fromDate, $toDate) {
    if ($range === 'custom' && $fromDate && $toDate) {
        return [
            'condition' => "AND timestamp BETWEEN :from_date AND :to_date",
            'params' => [
                ':from_date' => $fromDate . ' 00:00:00',
                ':to_date' => $toDate . ' 23:59:59'
            ]
        ];
    }
    
    $date = new DateTime();
    switch ($range) {
        case '7d':
            $date->modify('-7 days');
            break;
        case '30d':
            $date->modify('-30 days');
            break;
        case '90d':
            $date->modify('-90 days');
            break;
        default:
            $date->modify('-7 days');
    }
    
    return [
        'condition' => "AND timestamp >= :date",
        'params' => [':date' => $date->format('Y-m-d 00:00:00')]
    ];
}

function fetchVisitsData($pdo, $tenantId, $dateConditions) {
    $sql = "SELECT 
                DATE(timestamp) as date,
                COUNT(*) as visits
            FROM analytics_metrics
            WHERE tenant_id = :tenant_id
            {$dateConditions['condition']}
            GROUP BY DATE(timestamp)
            ORDER BY date ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':tenant_id', $tenantId);
    foreach ($dateConditions['params'] as $param => $value) {
        $stmt->bindValue($param, $value);
    }
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function formatVisitsData($data) {
    return [
        'labels' => array_column($data, 'date'),
        'datasets' => [[
            'label' => 'Visits',
            'data' => array_column($data, 'visits'),
            'borderColor' => 'rgb(75, 192, 192)',
            'tension' => 0.1
        ]]
    ];
}

// Similar functions for content, sources, and engagement data
// (Implementation omitted for brevity but follows same pattern)
