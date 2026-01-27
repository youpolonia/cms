<?php require_once __DIR__ . '/../../views/layout.php'; ?>
<?php startblock('content'); ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= htmlspecialchars($title) ?></h1>
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="get" class="mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <label>From Date</label>
                        <input type="date" name="from_date" class="form-control" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label>To Date</label>
                        <input type="date" name="to_date" class="form-control" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="<?= admin_url('activity') ?>" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-bordered" id="activityTable">
                    <thead>
                        <tr>
                            <th>Client Name</th>
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
                            <td>
                                <a href="<?= admin_url('activity/client/'.$activity['client_id']) ?>">
                                    <?= htmlspecialchars($activity['client_name'] ?? 'Unknown Client') ?>
                                </a>
                            </td>
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
    <div class="modal-dialog" role="document">
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
<?php endblock();
