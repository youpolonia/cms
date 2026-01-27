<?php require_once __DIR__ . '/../../views/layout.php'; ?>
<?php startblock('content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">
        <?= htmlspecialchars($title) ?> - Client <?= htmlspecialchars($clientId) ?>
    </h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Log</h6>
                </div>
                <div class="col-md-6 text-right">
                    <form class="form-inline">
                        <div class="form-group mr-2">
                            <label for="activityType" class="sr-only">Filter</label>
                            <select class="form-control form-control-sm" id="activityType">
                                <option value="">All Activities</option>
                                <option value="GET">Page Views</option>
                                <option value="POST">Form Submissions</option>
                                <option value="API">API Calls</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" id="applyFilter">
                            Apply
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="clientActivityTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Details</th>
                            <th>IP</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($activities as $activity): ?>
                        <tr>
                            <td><?= htmlspecialchars($activity['user_id'] ?? 'System') ?></td>
                            <td><?= htmlspecialchars($activity['activity_type']) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#detailsModal<?= $activity['id'] ?>">
                                    View Details
                                </button>
                            </td>
                            <td><?= htmlspecialchars($activity['ip_address']) ?></td>
                            <td><?= htmlspecialchars($activity['created_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php foreach($activities as $activity): ?>
<div class="modal fade" id="detailsModal<?= $activity['id'] ?>" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
            </div>
            <div class="modal-body">
                <pre><?= json_encode(json_decode($activity['activity_details']), JSON_PRETTY_PRINT) ?></pre>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<script>
$(document).ready(function() {
    $('#applyFilter').click(function() {
        const filter = $('#activityType').val();
        window.location.href = `?filter=${filter}`;
    });
});
</script>

<?php endblock();
