<?php
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
// Workflow Monitoring Dashboard
require_once __DIR__ . '/../includes/auth/admin-auth.php';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workflow Monitoring</title>
    <link rel="stylesheet" href="/admin/css/admin.css">
    <style>
        .dashboard {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .chart-container {
            height: 300px;
            position: relative;
        }
        .workflow-list {
            margin-top: 20px;
        }
        .workflow-item {
            padding: 10px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-running { background: #17a2b8; color: #fff; }
        .status-completed { background: #28a745; color: #fff; }
        .status-failed { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <?php require_once __DIR__ . '/admin-header.php'; 
?>    <main class="dashboard">
        <div class="card">
            <h2>Workflow Summary</h2>
            <div class="chart-container">
                <canvas id="workflowChart"></canvas>
            </div>
        </div>

        <div class="card">
            <h2>Active Workflows</h2>
            <div class="workflow-list" id="activeWorkflows">
                <!-- Will be populated by JS -->
            </div>
        </div>

        <div class="card">
            <h2>Recent Activity</h2>
            <div class="workflow-list" id="recentActivity">
                <!-- Will be populated by JS -->
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fetch workflow data from API
            fetch('/api/v1/workflows')
                .then(response => response.json())
                .then(data => {
                    renderWorkflowChart(data.stats);
                    renderActiveWorkflows(data.active);
                    renderRecentActivity(data.recent);
                });

            function renderWorkflowChart(stats) {
                const ctx = document.getElementById('workflowChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Pending', 'Running', 'Completed', 'Failed'],
                        datasets: [{
                            data: [stats.pending, stats.running, stats.completed, stats.failed],
                            backgroundColor: [
                                '#ffc107',
                                '#17a2b8', 
                                '#28a745',
                                '#dc3545'
                            ]
                        }]
                    }
                });
            }

            function renderActiveWorkflows(workflows) {
                const container = document.getElementById('activeWorkflows');
                workflows.forEach(wf => {
                    const item = document.createElement('div');
                    item.className = 'workflow-item';
                    item.innerHTML = `
                        <span>${wf.name}</span>
                        <span class="status-badge status-${wf.status.toLowerCase()}">${wf.status}</span>
                    `;
                    container.appendChild(item);
                });
            }

            function renderRecentActivity(activities) {
                const container = document.getElementById('recentActivity');
                activities.forEach(act => {
                    const item = document.createElement('div');
                    item.className = 'workflow-item';
                    item.innerHTML = `
                        <span>${act.workflow_name} - ${act.action}</span>
                        <small>${new Date(act.timestamp).toLocaleString()}</small>
                    `;
                    container.appendChild(item);
                });
            }
        });
    </script>
</body>
</html>
