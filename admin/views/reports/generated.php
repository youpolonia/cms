<?php
/**
 * Generated Report View
 */
?><div class="report-view">
    <div class="report-header">
        <h3><?php echo htmlspecialchars($title); ?></h3>
        <div class="report-actions">
            <a href="/admin/reports/export.php?type=<?php echo urlencode($reportType); ?>"
               class="btn btn-export">
               Export PDF
            </a>
            <a href="/admin/reports/export.php?type=<?php echo urlencode($reportType); ?>&format=xls"
               class="btn btn-export">
               Export Excel
            </a>
        </div>
    </div>

    <?php if (!empty($reportData['headers']) && !empty($reportData['rows'])): ?>
    <div class="report-table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <?php foreach ($reportData['headers'] as $header): ?>
                        <th><?php echo htmlspecialchars($header); ?></th>
                    <?php endforeach;  ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reportData['rows'] as $row): ?>
                    <tr>
                        <?php foreach ($row as $cell): ?>
                            <td><?php echo htmlspecialchars($cell); ?></td>
                        <?php endforeach;  ?>
                    </tr>
                <?php endforeach;  ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <div class="alert alert-info">No data available for this report</div>
    <?php endif;  ?>
</div>
