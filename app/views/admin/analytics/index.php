<?php
$title = 'Analytics';
ob_start();

function fmtNum($n) { return number_format((int)$n); }
function fmtDur($s) {
    $s = (int)$s;
    return $s < 60 ? $s . 's' : floor($s / 60) . 'm ' . ($s % 60) . 's';
}
function fmtChange($c) {
    $c = (float)$c;
    $cls = $c > 0 ? 'positive' : ($c < 0 ? 'negative' : 'neutral');
    $sign = $c > 0 ? '+' : '';
    $arrow = $c >= 0 ? '↑' : '↓';
    return '<span class="change ' . $cls . '">' . $arrow . ' ' . $sign . number_format($c, 1) . '%</span>';
}

$summary = $data['summary'] ?? [];
$comparison = $data['comparison'] ?? [];
$dailyStats = $data['daily_stats'] ?? [];
$topPages = $data['top_pages'] ?? [];
$devices = $data['devices'] ?? [];
$periodLabel = $data['period']['label'] ?? 'Last 7 Days';
?>

<style>
:root {
    --ctp-text: #cdd6f4; --ctp-subtext0: #a6adc8; --ctp-surface0: #313244;
    --ctp-surface1: #45475a; --ctp-surface2: #585b70; --ctp-blue: #89b4fa;
    --ctp-green: #a6e3a1; --ctp-peach: #fab387; --ctp-mauve: #cba6f7;
    --ctp-red: #f38ba8; --ctp-overlay1: #7f849c; --ctp-mantle: #181825;
}
.analytics-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
.analytics-title { font-size: 1.5rem; font-weight: 700; color: var(--ctp-text); }
.analytics-controls { display: flex; gap: 0.75rem; align-items: center; }
.period-selector { display: flex; background: var(--ctp-surface0); border-radius: 8px; padding: 4px; gap: 2px; }
.period-btn { padding: 8px 16px; border: none; background: transparent; color: var(--ctp-subtext0); font-size: 13px; font-weight: 500; border-radius: 6px; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.period-btn:hover { color: var(--ctp-text); background: var(--ctp-surface1); }
.period-btn.active { background: var(--ctp-blue); color: #1e1e2e; }
.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; padding: 1.25rem; }
.stat-card.highlight { background: linear-gradient(135deg, var(--ctp-surface0), rgba(137,180,250,0.1)); border-color: var(--ctp-blue); }
.stat-icon { font-size: 1.5rem; margin-bottom: 0.5rem; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: var(--ctp-text); }
.stat-label { font-size: 0.875rem; color: var(--ctp-subtext0); margin: 0.25rem 0 0.5rem; }
.change.positive { color: var(--ctp-green); } .change.negative { color: var(--ctp-red); } .change.neutral { color: var(--ctp-overlay1); }
.realtime-dot { display: inline-block; width: 8px; height: 8px; background: var(--ctp-green); border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
.chart-container { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
.chart-title { font-size: 1rem; font-weight: 600; color: var(--ctp-text); margin-bottom: 1rem; }
.chart-canvas { height: 280px; }
.grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem; }
@media (max-width: 1024px) { .grid-2 { grid-template-columns: 1fr; } }
.data-card { background: var(--ctp-surface0); border: 1px solid var(--ctp-surface1); border-radius: 12px; overflow: hidden; }
.data-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--ctp-surface1); font-weight: 600; color: var(--ctp-text); }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th { padding: 0.75rem 1.25rem; text-align: left; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; color: var(--ctp-subtext0); background: var(--ctp-mantle); }
.data-table td { padding: 0.75rem 1.25rem; font-size: 0.875rem; color: var(--ctp-subtext0); border-bottom: 1px solid var(--ctp-surface1); }
.data-table tr:hover { background: var(--ctp-surface1); }
.page-url { max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--ctp-blue); }
.metric { font-weight: 600; color: var(--ctp-text); }
.progress-list { padding: 1rem 1.25rem; }
.progress-item { margin-bottom: 1rem; }
.progress-label { display: flex; justify-content: space-between; margin-bottom: 0.375rem; font-size: 0.875rem; }
.progress-name { color: var(--ctp-subtext0); } .progress-value { color: var(--ctp-text); font-weight: 600; }
.progress-bar { height: 8px; background: var(--ctp-surface1); border-radius: 4px; overflow: hidden; }
.progress-fill { height: 100%; border-radius: 4px; }
.progress-fill.desktop { background: var(--ctp-blue); } .progress-fill.mobile { background: var(--ctp-green); }
.progress-fill.tablet { background: var(--ctp-peach); } .progress-fill.bot { background: var(--ctp-overlay1); }
.btn-export { background: var(--ctp-surface1); color: var(--ctp-text); border: 1px solid var(--ctp-surface2); padding: 8px 16px; font-size: 13px; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn-export:hover { background: var(--ctp-surface2); }
.empty { padding: 2rem; text-align: center; color: var(--ctp-subtext0); }
</style>

<div class="analytics-page">
    <div class="analytics-header">
        <div class="analytics-title">Analytics <span<span class="tip"><span class="tip-text">Site visitor and page view statistics.</span></span> style="font-size:0.875rem;font-weight:400;color:var(--ctp-subtext0);margin-left:0.5rem"><?= esc($periodLabel) ?></span></div>
        <div class="analytics-controls">
            <div class="period-selector">
                <?php foreach ($validPeriods as $p): ?>
                <a href="?period=<?= $p ?>" class="period-btn <?= $period === $p ? 'active' : '' ?>">
                    <?= match($p) { '24h'=>'24H', '7d'=>'7D', '30d'=>'30D', '90d'=>'90D', 'year'=>'Year' } ?>
                </a>
                <?php endforeach; ?>
            </div>
            <a href="/admin/analytics/export?start_date=<?= urlencode($data['period']['start'] ?? '') ?>&end_date=<?= urlencode($data['period']['end'] ?? '') ?>" class="btn-export">Export CSV</a>
        </div>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value"><?= fmtNum($summary['total_views'] ?? 0) ?></div>
            <div class="stat-label">Total Views</div>
            <?= fmtChange($comparison['views_change'] ?? 0) ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-value"><?= fmtNum($summary['unique_visitors'] ?? 0) ?></div>
            <div class="stat-label">Unique Visitors</div>
            <?= fmtChange($comparison['visitors_change'] ?? 0) ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🔄</div>
            <div class="stat-value"><?= fmtNum($summary['total_sessions'] ?? 0) ?></div>
            <div class="stat-label">Sessions</div>
            <?= fmtChange($comparison['sessions_change'] ?? 0) ?>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⏱️</div>
            <div class="stat-value"><?= fmtDur($summary['avg_duration'] ?? 0) ?></div>
            <div class="stat-label">Avg Duration</div>
            <?= fmtChange($comparison['duration_change'] ?? 0) ?>
        </div>
        <div class="stat-card highlight">
            <div class="stat-icon"><span class="realtime-dot"></span></div>
            <div class="stat-value" id="active-visitors"><?= (int)($summary['active_visitors'] ?? 0) ?></div>
            <div class="stat-label">Active Now</div>
            <span class="change neutral" id="realtime-status">Live</span>
        </div>
    </div>

    <div class="chart-container">
        <div class="chart-title">Traffic Overview</div>
        <div class="chart-canvas"><canvas id="trafficChart"></canvas></div>
    </div>

    <div class="grid-2">
        <div class="data-card">
            <div class="data-header">Top Pages</div>
            <?php if (empty($topPages)): ?>
                <div class="empty">No page views yet</div>
            <?php else: ?>
            <table class="data-table">
                <thead><tr><th>Page</th><th>Views</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($topPages, 0, 8) as $page): ?>
                <tr>
                    <td><div class="page-url" title="<?= esc($page['page_url'] ?? '') ?>"><?= esc($page['page_title'] ?? $page['page_url'] ?? '-') ?></div></td>
                    <td class="metric"><?= fmtNum($page['view_count'] ?? 0) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>

        <div class="data-card">
            <div class="data-header">Devices</div>
            <?php if (empty($devices)): ?>
                <div class="empty">No device data</div>
            <?php else: ?>
            <div class="progress-list">
                <?php foreach ($devices as $d): $type = strtolower($d['device_type'] ?? 'unknown'); ?>
                <div class="progress-item">
                    <div class="progress-label">
                        <span class="progress-name"><?= match($type) { 'desktop'=>'🖥 Desktop', 'mobile'=>'📱 Mobile', 'tablet'=>'📱 Tablet', 'bot'=>'🤖 Bot', default=>'❓ '.ucfirst($type) } ?></span>
                        <span class="progress-value"><?= number_format((float)($d['percentage'] ?? 0), 1) ?>%</span>
                    </div>
                    <div class="progress-bar"><div class="progress-fill <?= $type ?>" style="width:<?= (float)($d['percentage'] ?? 0) ?>%"></div></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stats = <?= json_encode($dailyStats) ?>;
    const labels = stats.map(d => { const dt = new Date(d.date || d.stat_date); return dt.toLocaleDateString('en-US', {month:'short',day:'numeric'}); });
    const views = stats.map(d => parseInt(d.views || d.total_views || 0));
    const visitors = stats.map(d => parseInt(d.unique_visitors || d.visitors || 0));
    const colors = { blue:'#89b4fa', green:'#a6e3a1', surface1:'#45475a', subtext:'#a6adc8' };

    new Chart(document.getElementById('trafficChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                { label: 'Views', data: views, borderColor: colors.blue, backgroundColor: colors.blue+'20', fill: true, tension: 0.4, pointRadius: 2 },
                { label: 'Visitors', data: visitors, borderColor: colors.green, backgroundColor: colors.green+'20', fill: true, tension: 0.4, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', align: 'end', labels: { color: colors.subtext, usePointStyle: true } } },
            scales: {
                x: { grid: { color: colors.surface1 }, ticks: { color: colors.subtext } },
                y: { beginAtZero: true, grid: { color: colors.surface1 }, ticks: { color: colors.subtext } }
            }
        }
    });

    setInterval(() => {
        fetch('/admin/analytics/realtime').then(r => r.json()).then(d => {
            if (d.success) document.getElementById('active-visitors').textContent = d.data.active_visitors || 0;
        }).catch(() => {});
    }, 30000);
});
</script>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
