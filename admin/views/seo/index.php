<?php
/**
 * SEO Dashboard View
 * Shows SEO statistics, low-scoring pages, and pages without SEO
 */

// $data contains: stats, low_score_pages, pages_without_seo
$stats = $data['stats'] ?? [];
$lowScorePages = $data['low_score_pages'] ?? [];
$pagesWithoutSeo = $data['pages_without_seo'] ?? [];

function esc($str) {
    return htmlspecialchars((string) $str, ENT_QUOTES, 'UTF-8');
}
?>
<div class="seo-dashboard">
    <h1>SEO Dashboard</h1>
    <p class="muted">Overview of your site's SEO health and performance.</p>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo (int) ($stats['total_with_seo'] ?? 0); ?></div>
            <div class="stat-label">Pages with SEO</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo esc($stats['avg_seo_score'] ?? '0'); ?>%</div>
            <div class="stat-label">Average SEO Score</div>
        </div>
        <div class="stat-card <?php echo ($stats['needs_attention'] ?? 0) > 0 ? 'warning' : ''; ?>">
            <div class="stat-value"><?php echo (int) ($stats['needs_attention'] ?? 0); ?></div>
            <div class="stat-label">Need Attention</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo (int) ($stats['active_redirects'] ?? 0); ?></div>
            <div class="stat-label">Active Redirects</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($stats['total_redirect_hits'] ?? 0); ?></div>
            <div class="stat-label">Redirect Hits</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo (int) ($stats['tracked_keywords'] ?? 0); ?></div>
            <div class="stat-label">Tracked Keywords</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
        <h2>Quick Actions</h2>
        <div class="button-group">
            <a href="seo-metadata.php" class="btn">Manage SEO Metadata</a>
            <a href="seo-redirects.php" class="btn">Manage Redirects</a>
            <a href="seo-keywords.php" class="btn">Keyword Research</a>
            <button type="button" class="btn primary" onclick="regenerateSitemap()">Regenerate Sitemap</button>
        </div>
    </div>

    <!-- Low Score Pages -->
    <?php if (!empty($lowScorePages)): ?>
    <div class="card">
        <h2>Pages Needing Attention</h2>
        <p class="muted">These pages have SEO scores below 50% and may need optimization.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>Type</th>
                    <th>SEO Score</th>
                    <th>Focus Keyword</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($lowScorePages as $page): ?>
                <tr>
                    <td><?php echo esc($page['entity_title']); ?></td>
                    <td><?php echo esc(ucfirst($page['entity_type'])); ?></td>
                    <td>
                        <span class="score-badge <?php echo ($page['seo_score'] ?? 0) < 30 ? 'poor' : 'needs-work'; ?>">
                            <?php echo (int) ($page['seo_score'] ?? 0); ?>%
                        </span>
                    </td>
                    <td><?php echo esc($page['focus_keyword'] ?? '-'); ?></td>
                    <td>
                        <a href="seo-edit.php?type=<?php echo esc($page['entity_type']); ?>&id=<?php echo (int) $page['entity_id']; ?>" class="btn small">Edit SEO</a>
                        <button type="button" class="btn small" onclick="analyzeContent('<?php echo esc($page['entity_type']); ?>', <?php echo (int) $page['entity_id']; ?>)">Analyze</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Pages Without SEO -->
    <?php if (!empty($pagesWithoutSeo)): ?>
    <div class="card">
        <h2>Pages Without SEO Data</h2>
        <p class="muted">These pages don't have any SEO metadata configured yet.</p>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Page Title</th>
                    <th>Slug</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pagesWithoutSeo as $page): ?>
                <tr>
                    <td><?php echo esc($page['title']); ?></td>
                    <td><code>/<?php echo esc($page['slug']); ?></code></td>
                    <td>
                        <a href="seo-edit.php?type=page&id=<?php echo (int) $page['id']; ?>" class="btn small primary">Add SEO</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Crawl Statistics -->
    <?php if (($stats['crawls_24h'] ?? 0) > 0): ?>
    <div class="card">
        <h2>Crawl Activity (Last 24 Hours)</h2>
        <div class="stats-inline">
            <div class="stat-item">
                <strong><?php echo (int) $stats['crawls_24h']; ?></strong>
                <span>Total Crawls</span>
            </div>
            <div class="stat-item">
                <strong><?php echo (int) $stats['successful_crawls_24h']; ?></strong>
                <span>Successful</span>
            </div>
            <div class="stat-item">
                <strong><?php echo (int) ($stats['crawls_24h'] - $stats['successful_crawls_24h']); ?></strong>
                <span>Errors</span>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function regenerateSitemap() {
    if (!confirm('Regenerate the XML sitemap?')) return;

    fetch('api/seo-actions.php?action=regenerate_sitemap', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Sitemap regenerated! ' + (data.url_count || 0) + ' URLs included.');
        } else {
            alert('Error: ' + (data.errors?.join(', ') || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}

function analyzeContent(type, id) {
    fetch('api/seo-actions.php?action=analyze&type=' + type + '&id=' + id, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content || ''
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('SEO Score: ' + data.seo_score + '%\nReadability: ' + data.readability_score + '%');
            location.reload();
        } else {
            alert('Error: ' + (data.errors?.join(', ') || 'Unknown error'));
        }
    })
    .catch(err => alert('Request failed: ' + err.message));
}
</script>

<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 1.25rem;
    text-align: center;
}
.stat-card.warning {
    border-color: #f0ad4e;
    background: #fcf8e3;
}
.stat-value {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}
.stat-label {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}
.score-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-weight: bold;
    font-size: 0.875rem;
}
.score-badge.poor {
    background: #f8d7da;
    color: #721c24;
}
.score-badge.needs-work {
    background: #fff3cd;
    color: #856404;
}
.score-badge.good {
    background: #d4edda;
    color: #155724;
}
.stats-inline {
    display: flex;
    gap: 2rem;
}
.stat-item {
    display: flex;
    flex-direction: column;
}
.stat-item strong {
    font-size: 1.5rem;
}
.stat-item span {
    color: #666;
    font-size: 0.875rem;
}
.button-group {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
</style>
