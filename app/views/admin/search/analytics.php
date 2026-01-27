<?php
$title = 'Search Analytics';
ob_start();
?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?= esc($success) ?></div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Top Searches (30 days)</h2>
        </div>
        <?php if (empty($topSearches)): ?>
            <div class="card-body">
                <p style="color: var(--text-muted);">No search data yet.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Count</th>
                        <th>Avg Results</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topSearches as $search): ?>
                        <tr>
                            <td><code><?= esc($search['query']) ?></code></td>
                            <td><?= (int)$search['count'] ?></td>
                            <td><?= number_format((float)$search['avg_results'], 1) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Searches with No Results</h2>
        </div>
        <?php if (empty($noResults)): ?>
            <div class="card-body">
                <p style="color: var(--text-muted);">No failed searches.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($noResults as $search): ?>
                        <tr>
                            <td><code><?= esc($search['query']) ?></code></td>
                            <td><?= (int)$search['count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <div class="card-header">
        <h2 class="card-title">Daily Search Volume</h2>
        <div style="display: flex; gap: 0.5rem;">
            <a href="/admin/search" class="btn btn-secondary btn-sm">← Back to Search</a>
            <form method="post" action="/admin/search/clear" onsubmit="return confirm('Clear all search logs?');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm">Clear Logs</button>
            </form>
        </div>
    </div>
    <?php if (empty($dailyStats)): ?>
        <div class="card-body">
            <p style="color: var(--text-muted);">No search data yet.</p>
        </div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Searches</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dailyStats as $stat): ?>
                    <tr>
                        <td><?= date('M j, Y', strtotime($stat['date'])) ?></td>
                        <td><?= (int)$stat['count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
