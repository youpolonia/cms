<?php
class StatisticsWidget {
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService) {
        $this->auditLogService = $auditLogService;
    }

    public static function render(): string {
        $service = new self(new AuditLogService(new Database()));
        $stats = $service->auditLogService->getStatistics();

        ob_start();
        ?>        <div class="dashboard-widget statistics">
            <h3>System Statistics</h3>
            <div class="stat-grid">
                <div class="stat-item">
                    <span class="stat-value"><?= $stats['total_actions'] ?></span>
                    <span class="stat-label">Total Actions</span>
                </div>
                <div class="stat-item">
                    <span class="stat-value"><?= count($stats['recent_users']) ?></span>
                    <span class="stat-label">Active Users</span>
                </div>
            </div>
            <div class="action-types">
                <h4>Top Actions</h4>
                <ul>
                    <?php foreach($stats['actions_by_type'] as $action): ?>
                        <li>
                        <span class="action-name"><?= $action['action'] ?></span>
                        <span class="action-count"><?= $action['count'] ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
