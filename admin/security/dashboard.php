<?php
require_once __DIR__ . '/../../includes/security/securityauditor.php';
require_once __DIR__ . '/../../includes/auth/check_auth.php';

// Check admin permissions
if (!has_permission('security_dashboard')) {
    header('HTTP/1.0 403 Forbidden');
    exit('Access denied');
}

$auditor = new SecurityAuditor();
$results = $auditor->audit();

// Group results by severity
$groupedResults = [
    'critical' => [],
    'high' => [],
    'medium' => [],
    'low' => []
];

foreach ($results as $result) {
    $groupedResults[$result['severity']][] = $result;
}

// Calculate stats
$stats = [
    'total' => count($results),
    'critical' => count($groupedResults['critical']),
    'high' => count($groupedResults['high']),
    'medium' => count($groupedResults['medium']),
    'low' => count($groupedResults['low'])
];
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Security Dashboard</title>
    <link rel="stylesheet" href="/admin/assets/css/main.css">
    <style>
        .security-card {
            border-radius: 4px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .critical { background-color: #ffebee; border-left: 4px solid #f44336; }
        .high { background-color: #fff8e1; border-left: 4px solid #ffc107; }
        .medium { background-color: #e8f5e9; border-left: 4px solid #4caf50; }
        .low { background-color: #e3f2fd; border-left: 4px solid #2196f3; }
        .severity-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .badge-critical { background-color: #f44336; }
        .badge-high { background-color: #ff9800; }
        .badge-medium { background-color: #4caf50; }
        .badge-low { background-color: #2196f3; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/admin/header.php'; 
?>    <main class="container">
        <h1>Security Dashboard</h1>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Issues</h5>
                        <h2><?= $stats['total'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Critical</h5>
                        <h2 class="text-danger"><?= $stats['critical'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">High</h5>
                        <h2 class="text-warning"><?= $stats['high'] ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Medium/Low</h5>
                        <h2><?= $stats['medium'] + $stats['low'] ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <button class="btn btn-primary" onclick="runNewScan()">Run New Scan</button>
            <button class="btn btn-secondary" onclick="downloadReport()">Download Report</button>
        </div>

        <div class="mt-4">
            <?php foreach (['critical', 'high', 'medium', 'low'] as $severity): ?>                <?php if (!empty($groupedResults[$severity])): ?>
                    <div class="security-card <?= $severity ?>">
                        <h3>
                            <span class="severity-badge badge-<?= $severity ?>">
                                <?= ucfirst($severity)  ?>
                            </span>
                            (<?= count($groupedResults[$severity]) ?>) ?>
                        </h3>
                        
                        <div class="list-group mt-3">
                            <?php foreach ($groupedResults[$severity] as $issue): ?>
                                <div class="list-group-item">
                                    <h5><?= htmlspecialchars($issue['type']) ?></h5>
                                    <p><?= htmlspecialchars($issue['message']) ?></p>
                                    <small class="text-muted"><?= htmlspecialchars($issue['file']) ?></small>
                                </div>
                            <?php endforeach;  ?>
                        </div>
                    </div>
                <?php endif;  ?>            <?php endforeach;  ?>
        </div>
    </main>

    <script>
        function runNewScan() {
            fetch('/admin/security/scan.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?>'                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Scan failed: ' + data.message);
                }
            });
        }

        function downloadReport() {
            window.location.href = '/admin/security/report.php';
        }
    </script>
</body>
</html>
