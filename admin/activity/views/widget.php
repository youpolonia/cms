<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Recent Client Activities</h6>
        <a href="<?= admin_url('activity') ?>" class="btn btn-sm btn-link">View All</a>
    </div>
    <div class="card-body">
        <div class="activity-feed">
            <?php foreach($activities as $activity): ?>
            <div class="activity-item mb-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <strong><?= htmlspecialchars($activity['activity_type']) ?></strong>
                        <small class="text-muted d-block">
                            Client <?= htmlspecialchars($activity['client_id']) ?>
                        </small>
                    </div>
                    <small class="text-muted">
                        <?= htmlspecialchars(date('H:i', strtotime($activity['created_at']))) ?>
                    </small>
                </div>
                <div class="text-truncate small">
                    <?= htmlspecialchars(substr($activity['activity_details'], 0, 50)) ?>...
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
