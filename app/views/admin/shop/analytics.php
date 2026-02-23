<?php
if (!function_exists('h')) { function h($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
$pageTitle = '📈 Shop Analytics';
$currency = get_setting('shop_currency', 'USD');
$symbols = ['USD'=>'$','EUR'=>'€','GBP'=>'£','PLN'=>'zł'];
$sym = $symbols[$currency] ?? $currency . ' ';

$fmtMoney = fn($v) => $sym . number_format((float)$v, 2);
$fmtNum = fn($v) => number_format((float)$v, 0, '.', ',');
$fmtPct = fn($v) => number_format((float)$v, 1) . '%';

// Compute chart geometry
$chartW = 800;
$chartH = 300;
$padL = 60;
$padR = 20;
$padT = 20;
$padB = 40;
$innerW = $chartW - $padL - $padR;
$innerH = $chartH - $padT - $padB;

$maxRev = 0.01;
foreach ($revenueChart as $d) {
    if ($d['revenue'] > $maxRev) $maxRev = $d['revenue'];
}
$maxRev = ceil($maxRev / 10) * 10;
if ($maxRev < 1) $maxRev = 100;

$points = [];
$circles = [];
$count = count($revenueChart);
foreach ($revenueChart as $i => $d) {
    $x = $count > 1 ? $padL + ($i / ($count - 1)) * $innerW : $padL + $innerW / 2;
    $y = $padT + $innerH - ($d['revenue'] / $maxRev) * $innerH;
    $points[] = round($x, 1) . ',' . round($y, 1);
    $circles[] = ['x' => round($x, 1), 'y' => round($y, 1), 'date' => $d['date'], 'revenue' => $d['revenue'], 'orders' => $d['orders']];
}
$polyline = implode(' ', $points);
$areaPoints = ($padL . ',' . ($padT + $innerH)) . ' ' . $polyline . ' ' . (round($padL + (($count - 1) / max(1, $count - 1)) * $innerW, 1) . ',' . ($padT + $innerH));

// Funnel percentages
$funnelSteps = [
    ['key' => 'views', 'label' => 'Product Views', 'color' => '#89b4fa'],
    ['key' => 'add_to_cart', 'label' => 'Add to Cart', 'color' => '#a6e3a1'],
    ['key' => 'checkout', 'label' => 'Checkout', 'color' => '#fab387'],
    ['key' => 'purchase', 'label' => 'Purchase', 'color' => '#f38ba8'],
];
$funnelMax = max(1, $funnel['views']);

// Peak hours max
$hourlyMax = 1;
foreach ($hourly as $h) {
    if ($h['orders'] > $hourlyMax) $hourlyMax = $h['orders'];
}

// Top categories max
$catMax = 0.01;
foreach ($topCategories as $c) {
    if ($c['revenue'] > $catMax) $catMax = $c['revenue'];
}

ob_start();
?>
<style>
.sa-wrap{max-width:1280px;margin:0 auto;padding:24px 20px}
.sa-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.sa-header h1{font-size:1.5rem;font-weight:700;color:#cdd6f4;margin:0}
.sa-period{display:flex;gap:6px}
.sa-period a{padding:6px 16px;border-radius:8px;font-size:.8rem;font-weight:600;text-decoration:none;background:#313244;color:#a6adc8;border:1px solid #45475a;transition:all .2s}
.sa-period a.active,.sa-period a:hover{background:#89b4fa;color:#1e1e2e;border-color:#89b4fa}

/* KPI Cards */
.sa-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-bottom:28px}
.sa-kpi{background:#313244;border-radius:14px;padding:22px;border:1px solid #45475a;position:relative;overflow:hidden;animation:fadeUp .5s ease both}
.sa-kpi:nth-child(2){animation-delay:.08s}
.sa-kpi:nth-child(3){animation-delay:.16s}
.sa-kpi:nth-child(4){animation-delay:.24s}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.sa-kpi .icon{font-size:1.6rem;margin-bottom:8px}
.sa-kpi .value{font-size:1.8rem;font-weight:800;color:#cdd6f4;line-height:1.1}
.sa-kpi .label{font-size:.75rem;color:#a6adc8;margin-top:4px;text-transform:uppercase;letter-spacing:.04em}
.sa-kpi .change{font-size:.75rem;font-weight:700;margin-top:8px;display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:6px}
.sa-kpi .change.up{background:rgba(166,227,161,.15);color:#a6e3a1}
.sa-kpi .change.down{background:rgba(243,139,168,.15);color:#f38ba8}
.sa-kpi .change.flat{background:rgba(166,173,200,.1);color:#a6adc8}

/* Chart */
.sa-chart-wrap{background:#313244;border-radius:14px;padding:24px;border:1px solid #45475a;margin-bottom:28px;animation:fadeUp .5s ease .3s both}
.sa-chart-title{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#a6adc8;margin:0 0 16px;font-weight:600}
.sa-svg-wrap{width:100%;overflow-x:auto}
.sa-svg-wrap svg{display:block;width:100%;height:auto}
.sa-svg-wrap svg text{font-family:inherit;font-size:10px;fill:#6c7086}
.sa-tooltip{position:absolute;background:#1e1e2e;border:1px solid #45475a;border-radius:8px;padding:8px 12px;font-size:.75rem;color:#cdd6f4;pointer-events:none;opacity:0;transition:opacity .15s;z-index:10;white-space:nowrap}
.chart-dot{fill:#89b4fa;cursor:pointer;transition:r .15s}
.chart-dot:hover{r:6}
.dot-group:hover .sa-tooltip{opacity:1}

/* Two columns */
.sa-cols{display:grid;grid-template-columns:1fr 1fr;gap:24px}
@media(max-width:900px){.sa-cols{grid-template-columns:1fr}}
.sa-card{background:#313244;border-radius:14px;padding:22px;border:1px solid #45475a;margin-bottom:20px;animation:fadeUp .5s ease .4s both}
.sa-card h3{font-size:.85rem;text-transform:uppercase;letter-spacing:.05em;color:#a6adc8;margin:0 0 16px;font-weight:600}

/* Tables */
.sa-tbl{width:100%;border-collapse:collapse;font-size:.82rem}
.sa-tbl th,.sa-tbl td{padding:8px 10px;text-align:left;border-bottom:1px solid #45475a}
.sa-tbl th{color:#6c7086;font-weight:600;font-size:.72rem;text-transform:uppercase}
.sa-tbl tr:last-child td{border-bottom:none}
.sa-tbl td{color:#cdd6f4}
.sa-tbl .rank{color:#6c7086;font-weight:700;width:30px}

/* Funnel */
.sa-funnel{display:flex;flex-direction:column;gap:10px}
.sa-funnel-step{position:relative}
.sa-funnel-bar{height:38px;border-radius:8px;display:flex;align-items:center;justify-content:space-between;padding:0 14px;font-size:.8rem;font-weight:600;transition:width .6s ease}
.sa-funnel-step .lbl{color:#cdd6f4}
.sa-funnel-step .num{color:rgba(205,214,244,.7);font-size:.75rem}

/* Category bars */
.sa-cat-bar-wrap{margin-bottom:10px}
.sa-cat-label{display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:4px}
.sa-cat-label .name{color:#cdd6f4}
.sa-cat-label .val{color:#a6adc8}
.sa-cat-bar{height:8px;background:#45475a;border-radius:4px;overflow:hidden}
.sa-cat-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,#89b4fa,#b4befe);transition:width .6s ease}

/* Search list */
.sa-search-item{display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #45475a;font-size:.82rem}
.sa-search-item:last-child{border:none}
.sa-search-item .q{color:#cdd6f4}
.sa-search-item .c{color:#a6adc8;font-weight:600}

/* Hourly chart */
.sa-hourly{display:flex;align-items:flex-end;gap:3px;height:80px}
.sa-hourly-bar{flex:1;background:#89b4fa;border-radius:3px 3px 0 0;min-width:6px;transition:height .4s ease;position:relative}
.sa-hourly-bar:hover{background:#b4befe}
.sa-hourly-bar[title]:hover::after{content:attr(title);position:absolute;bottom:calc(100% + 4px);left:50%;transform:translateX(-50%);background:#1e1e2e;border:1px solid #45475a;padding:2px 6px;border-radius:4px;font-size:.65rem;color:#cdd6f4;white-space:nowrap}
.sa-hourly-labels{display:flex;gap:3px;margin-top:4px}
.sa-hourly-labels span{flex:1;text-align:center;font-size:.55rem;color:#6c7086}
.sa-empty{color:#6c7086;font-size:.85rem;padding:20px 0;text-align:center}
</style>

<div class="sa-wrap">
    <div class="sa-header">
        <h1>📈 Shop Analytics</h1>
        <div class="sa-period">
            <a href="?days=7" class="<?= $days == 7 ? 'active' : '' ?>">7 days</a>
            <a href="?days=30" class="<?= $days == 30 ? 'active' : '' ?>">30 days</a>
            <a href="?days=90" class="<?= $days == 90 ? 'active' : '' ?>">90 days</a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="sa-kpis">
        <div class="sa-kpi">
            <div class="icon">💰</div>
            <div class="value"><?= h($fmtMoney($kpis['revenue'])) ?></div>
            <div class="label">Total Revenue</div>
            <?php $ch = $kpis['revenue_change']; $cls = $ch > 0 ? 'up' : ($ch < 0 ? 'down' : 'flat'); ?>
            <span class="change <?= $cls ?>"><?= $ch > 0 ? '↑' : ($ch < 0 ? '↓' : '—') ?> <?= h($fmtPct(abs($ch))) ?></span>
        </div>
        <div class="sa-kpi">
            <div class="icon">📦</div>
            <div class="value"><?= h($fmtNum($kpis['orders'])) ?></div>
            <div class="label">Total Orders</div>
            <?php $ch = $kpis['orders_change']; $cls = $ch > 0 ? 'up' : ($ch < 0 ? 'down' : 'flat'); ?>
            <span class="change <?= $cls ?>"><?= $ch > 0 ? '↑' : ($ch < 0 ? '↓' : '—') ?> <?= h($fmtPct(abs($ch))) ?></span>
        </div>
        <div class="sa-kpi">
            <div class="icon">🧾</div>
            <div class="value"><?= h($fmtMoney($kpis['aov'])) ?></div>
            <div class="label">Avg Order Value</div>
            <?php
                $aovCh = $kpis['prev_aov'] > 0 ? round((($kpis['aov'] - $kpis['prev_aov']) / $kpis['prev_aov']) * 100, 1) : 0;
                $cls = $aovCh > 0 ? 'up' : ($aovCh < 0 ? 'down' : 'flat');
            ?>
            <span class="change <?= $cls ?>"><?= $aovCh > 0 ? '↑' : ($aovCh < 0 ? '↓' : '—') ?> <?= h($fmtPct(abs($aovCh))) ?></span>
        </div>
        <div class="sa-kpi">
            <div class="icon">🎯</div>
            <div class="value"><?= h($fmtPct($kpis['conversion_rate'])) ?></div>
            <div class="label">Conversion Rate</div>
            <span class="change flat">views → purchases</span>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="sa-chart-wrap">
        <h3 class="sa-chart-title">Revenue — Last <?= (int)$days ?> Days</h3>
        <div class="sa-svg-wrap" style="position:relative">
            <svg viewBox="0 0 <?= $chartW ?> <?= $chartH ?>" preserveAspectRatio="xMidYMid meet">
                <defs>
                    <linearGradient id="areaGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="rgba(137,180,250,0.35)"/>
                        <stop offset="100%" stop-color="rgba(137,180,250,0.02)"/>
                    </linearGradient>
                </defs>

                <!-- Grid lines -->
                <?php for ($g = 0; $g <= 4; $g++): ?>
                    <?php
                        $gy = $padT + ($g / 4) * $innerH;
                        $gVal = $maxRev - ($g / 4) * $maxRev;
                    ?>
                    <line x1="<?= $padL ?>" y1="<?= round($gy,1) ?>" x2="<?= $chartW - $padR ?>" y2="<?= round($gy,1) ?>" stroke="#45475a" stroke-width="0.5"/>
                    <text x="<?= $padL - 8 ?>" y="<?= round($gy + 3,1) ?>" text-anchor="end" fill="#6c7086" font-size="9"><?= $sym . number_format($gVal, 0) ?></text>
                <?php endfor; ?>

                <!-- Area -->
                <?php if ($count > 1): ?>
                <polygon points="<?= $areaPoints ?>" fill="url(#areaGrad)"/>
                <polyline points="<?= $polyline ?>" fill="none" stroke="#89b4fa" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                <?php endif; ?>

                <!-- Data points -->
                <?php foreach ($circles as $i => $c): ?>
                <g class="dot-group">
                    <circle class="chart-dot" cx="<?= $c['x'] ?>" cy="<?= $c['y'] ?>" r="3.5"/>
                    <circle cx="<?= $c['x'] ?>" cy="<?= $c['y'] ?>" r="14" fill="transparent"/>
                    <g class="sa-tooltip" transform="translate(<?= $c['x'] - 50 ?>,<?= $c['y'] - 52 ?>)">
                        <rect x="0" y="0" width="100" height="40" rx="6" fill="#1e1e2e" stroke="#45475a"/>
                        <text x="50" y="16" text-anchor="middle" fill="#cdd6f4" font-size="9" font-weight="600"><?= h($sym . number_format($c['revenue'], 2)) ?></text>
                        <text x="50" y="30" text-anchor="middle" fill="#6c7086" font-size="8"><?= h($c['date']) ?> · <?= $c['orders'] ?> orders</text>
                    </g>
                </g>
                <?php endforeach; ?>

                <!-- X axis labels -->
                <?php
                $step = max(1, (int)ceil($count / 8));
                foreach ($circles as $i => $c):
                    if ($i % $step === 0 || $i === $count - 1):
                ?>
                <text x="<?= $c['x'] ?>" y="<?= $chartH - 6 ?>" text-anchor="middle" fill="#6c7086" font-size="8"><?= date('M j', strtotime($c['date'])) ?></text>
                <?php endif; endforeach; ?>
            </svg>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="sa-cols">
        <!-- Left Column -->
        <div>
            <!-- Bestsellers -->
            <div class="sa-card">
                <h3>🏆 Bestsellers</h3>
                <?php if (empty($bestsellers)): ?>
                    <div class="sa-empty">No sales data yet</div>
                <?php else: ?>
                <table class="sa-tbl">
                    <thead><tr><th>#</th><th>Product</th><th>Units</th><th>Revenue</th></tr></thead>
                    <tbody>
                    <?php foreach ($bestsellers as $i => $b): ?>
                    <tr>
                        <td class="rank"><?= $i + 1 ?></td>
                        <td><?= h($b['name']) ?></td>
                        <td><?= h($fmtNum($b['units'])) ?></td>
                        <td><?= h($fmtMoney($b['revenue'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Top Categories -->
            <div class="sa-card">
                <h3>📁 Top Categories</h3>
                <?php if (empty($topCategories)): ?>
                    <div class="sa-empty">No category data yet</div>
                <?php else: ?>
                <?php foreach ($topCategories as $c): ?>
                <div class="sa-cat-bar-wrap">
                    <div class="sa-cat-label">
                        <span class="name"><?= h($c['name']) ?></span>
                        <span class="val"><?= h($fmtMoney($c['revenue'])) ?></span>
                    </div>
                    <div class="sa-cat-bar">
                        <div class="sa-cat-bar-fill" style="width:<?= round(($c['revenue'] / $catMax) * 100, 1) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column -->
        <div>
            <!-- Conversion Funnel -->
            <div class="sa-card">
                <h3>🔄 Conversion Funnel</h3>
                <div class="sa-funnel">
                <?php foreach ($funnelSteps as $si => $step):
                    $val = $funnel[$step['key']];
                    $pct = $funnelMax > 0 ? ($val / $funnelMax) * 100 : 0;
                    $width = max(25, $pct);
                ?>
                <div class="sa-funnel-step">
                    <div class="sa-funnel-bar" style="width:<?= round($width, 1) ?>%;background:<?= $step['color'] ?>22;border-left:4px solid <?= $step['color'] ?>">
                        <span class="lbl"><?= h($step['label']) ?></span>
                        <span class="num"><?= h($fmtNum($val)) ?> (<?= h($fmtPct($pct)) ?>)</span>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>

            <!-- Popular Searches -->
            <div class="sa-card">
                <h3>🔍 Popular Searches</h3>
                <?php if (empty($popularSearches)): ?>
                    <div class="sa-empty">No search data yet</div>
                <?php else: ?>
                <?php foreach ($popularSearches as $s): ?>
                <div class="sa-search-item">
                    <span class="q"><?= h($s['query'] ?? '') ?></span>
                    <span class="c"><?= h($fmtNum($s['cnt'])) ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Peak Hours -->
            <div class="sa-card">
                <h3>⏰ Peak Hours</h3>
                <div class="sa-hourly">
                    <?php foreach ($hourly as $h): ?>
                    <div class="sa-hourly-bar" style="height:<?= $hourlyMax > 0 ? max(2, round(($h['orders'] / $hourlyMax) * 100)) : 2 ?>%" title="<?= sprintf('%02d:00 — %d orders', $h['hour'], $h['orders']) ?>"></div>
                    <?php endforeach; ?>
                </div>
                <div class="sa-hourly-labels">
                    <?php for ($hh = 0; $hh < 24; $hh++): ?>
                    <span><?= $hh % 4 === 0 ? sprintf('%02d', $hh) : '' ?></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
$title = $pageTitle;
require CMS_APP . '/views/admin/layouts/topbar.php';
