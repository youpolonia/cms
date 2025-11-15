<?php
/**
 * Client Activity Dashboard View
 */
require_once __DIR__ . '/../../includes/header.php';

?><div class="container-fluid">
    <h1 class="mb-4">Client Activity Dashboard</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Activity Report</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="clientActivityTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <?php foreach ($reportData['headers'] as $header): ?>
                                <th><?= htmlspecialchars($header) ?></th>
                            <?php endforeach;  ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData['rows'] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row[0]) ?></td>
                                <td><?= htmlspecialchars($row[1]) ?></td>
                                <td>
                                    <?php if (is_array($row[2])): ?>
                                        <pre><?= print_r($row[2], true) ?></pre>
                                    <?php else: ?>                                        <?= htmlspecialchars($row[2])  ?>                                    <?php endif;  ?>
                                </td>
                                <td><?= htmlspecialchars($row[3]) ?></td>
                            </tr>
                        <?php endforeach;  ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php';
