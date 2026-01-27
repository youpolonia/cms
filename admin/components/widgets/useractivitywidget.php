<?php
class UserActivityWidget {
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService) {
        $this->auditLogService = $auditLogService;
    }

    public static function render(): string {
        $service = new self(new AuditLogService(new Database()));
        $recentActivity = $service->auditLogService->getLogs([
            'limit' => 5,
            'order' => 'DESC'
        ]);
        
        ob_start();
        ?>        <div class="dashboard-widget user-activity">
            <h3>User Activity</h3>
            <ul>
                <?php foreach($recentActivity as $log): ?>
                    <li>
                    <span class="user"><?= $log['username'] ?></span>
                    <span class="action"><?= $log['action'] ?></span>
                    <span class="time"><?= formatTimeAgo($log['created_at']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }
}
