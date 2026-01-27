<?php
/**
 * Integration Dashboard View
 * Version: 1.0
 */

// Check authentication
if (!defined('API_USER_ID')) {
    header('Location: /admin/login');
    exit;
}

$webhookService = new WebhookService();
$webSubHub = new WebSubHub();
$apiStats = $this->getApiStatistics();

?><div class="integration-dashboard">
    <h1>Integration Dashboard</h1>
    
    <div class="dashboard-grid">
        <!-- API Status Card -->
        <div class="dashboard-card">
            <h2>Headless API</h2>
            <div class="status-indicator <?= $apiStats['status'] ?>"></div>
            <p>Requests: <?= $apiStats['requests'] ?></p>
            <p>Errors: <?= $apiStats['errors'] ?></p>
            <a href="/admin/integration/api-settings" class="btn">Configure</a>
        </div>

        <!-- Webhooks Card -->
        <div class="dashboard-card">
            <h2>Webhooks</h2>
            <p>Active: <?= count($webhookService->getActiveWebhooks()) ?></p>
            <p>Pending Retries: <?= count($webhookService->getRetryQueue()) ?></p>
            <a href="/admin/integration/webhooks" class="btn">Manage</a>
        </div>

        <!-- WebSub Card -->
        <div class="dashboard-card">
            <h2>Content Federation</h2>
            <p>Active Subscribers: <?= count($webSubHub->getActiveSubscriptions()) ?></p>
            <p>Last Update: <?= $webSubHub->getLastUpdateTime() ?></p>
            <a href="/admin/integration/websub" class="btn">Configure</a>
        </div>

        <!-- Health Monitor -->
        <div class="dashboard-card health-monitor">
            <h2>System Health</h2>
            <div class="health-metrics">
                <div class="metric">
                    <span class="label">API Response Time</span>
                    <span class="value <?= $apiStats['response_time'] > 500 ? 'warning' : '' ?>">
                        <?= $apiStats['response_time'] ?>ms
                    </span>
                </div>
                <div class="metric">
                    <span class="label">Webhook Success Rate</span>
                    <span class="value <?= $apiStats['webhook_success'] < 90 ? 'warning' : '' ?>">
                        <?= $apiStats['webhook_success'] ?>%
                    </span>
                </div>
            </div>
            <a href="/admin/integration/health" class="btn">Details</a>
        </div>
    </div>

    <!-- Documentation Section -->
    <div class="documentation-section">
        <h2>Integration Documentation</h2>
        <div class="doc-links">
            <a href="/admin/integration/docs/api" class="doc-link">API Reference</a>
            <a href="/admin/integration/docs/webhooks" class="doc-link">Webhooks Guide</a>
            <a href="/admin/integration/docs/websub" class="doc-link">Content Federation</a>
        </div>
    </div>
</div>

<style>
.integration-dashboard {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.dashboard-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
.status-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 10px;
}
.status-indicator.healthy {
    background: #4CAF50;
}
.status-indicator.warning {
    background: #FFC107;
}
.status-indicator.critical {
    background: #F44336;
}
.health-metrics .metric {
    margin: 10px 0;
}
.health-metrics .value.warning {
    color: #FFC107;
    font-weight: bold;
}
.documentation-section {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
}
.doc-links {
    display: flex;
    gap: 15px;
    margin-top: 15px;
}
.doc-link {
    padding: 10px 15px;
    background: #2196F3;
    color: white;
    text-decoration: none;
    border-radius: 4px;
}
</style>
