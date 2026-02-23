<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$pageTitle = 'Content Calendar';
ob_start();
$monthNames = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
$dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
$prevMonth = $month - 1; $prevYear = $year;
$nextMonth = $month + 1; $nextYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }
$today = (int)date('j');
$isCurrentMonth = ($month === (int)date('n') && $year === (int)date('Y'));

$typeColors = ['article' => '#6366f1', 'page' => '#22c55e', 'social' => '#f59e0b', 'product' => '#ec4899'];
$statusStyles = ['published' => 'opacity:1', 'draft' => 'opacity:.6;border-style:dashed', 'scheduled' => 'opacity:.8;border-left:3px solid #3b82f6'];
$platformIcons = ['twitter' => '𝕏', 'facebook' => 'f', 'linkedin' => 'in', 'instagram' => '📷'];
?>
<style>
.cc-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
.cc-nav{display:flex;align-items:center;gap:12px}
.cc-nav a{padding:8px 12px;border-radius:8px;background:#334155;color:#e2e8f0;text-decoration:none;font-size:.85rem}
.cc-nav a:hover{background:#475569}
.cc-nav h2{font-size:1.3rem;font-weight:700;color:var(--text,#e2e8f0);min-width:200px;text-align:center}
.cc-stats{display:flex;gap:16px}
.cc-stats span{font-size:.8rem;color:var(--muted,#94a3b8)}
.cc-stats strong{color:var(--text,#e2e8f0)}
.cc-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:1px;background:var(--border,#334155);border:1px solid var(--border,#334155);border-radius:10px;overflow:hidden}
.cc-day-name{padding:10px;text-align:center;font-size:.75rem;font-weight:600;color:var(--muted,#94a3b8);background:var(--bg,#0f172a);text-transform:uppercase}
.cc-cell{min-height:100px;padding:6px;background:var(--bg-card,#1e293b);vertical-align:top}
.cc-cell.empty{background:var(--bg,#0f172a);opacity:.5}
.cc-cell.today{background:#6366f111;box-shadow:inset 0 0 0 2px #6366f1}
.cc-date{font-size:.75rem;font-weight:600;color:var(--muted,#94a3b8);margin-bottom:4px}
.cc-event{display:block;padding:3px 6px;margin-bottom:3px;border-radius:4px;font-size:.7rem;color:#fff;text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.4}
.cc-event:hover{opacity:.85;filter:brightness(1.1)}
.cc-legend{display:flex;gap:16px;margin-top:12px;justify-content:center}
.cc-legend span{display:flex;align-items:center;gap:4px;font-size:.75rem;color:var(--muted,#94a3b8)}
.cc-legend .dot{width:10px;height:10px;border-radius:3px}
@media(max-width:768px){.cc-cell{min-height:60px}.cc-event{font-size:.6rem;padding:2px 4px}}
</style>

<div class="cc-head">
    <div class="cc-nav">
        <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">← Prev</a>
        <h2><?= $monthNames[$month] ?> <?= $year ?></h2>
        <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next →</a>
    </div>
    <div class="cc-stats">
        <span>📰 Articles: <strong><?= $stats['articles_this_month'] ?></strong></span>
        <span>📄 Pages: <strong><?= $stats['pages_this_month'] ?></strong></span>
        <span>📅 Total: <strong><?= $stats['total_events'] ?></strong></span>
    </div>
</div>

<div class="cc-grid">
    <?php foreach ($dayNames as $dn): ?>
        <div class="cc-day-name"><?= $dn ?></div>
    <?php endforeach; ?>

    <?php
    // Empty cells before first day
    for ($i = 0; $i < $startWeekday; $i++):
    ?>
        <div class="cc-cell empty"></div>
    <?php endfor; ?>

    <?php for ($day = 1; $day <= $daysInMonth; $day++):
        $isToday = $isCurrentMonth && $day === $today;
        $dayEvents = $events[$day] ?? [];
    ?>
        <div class="cc-cell<?= $isToday ? ' today' : '' ?>">
            <div class="cc-date"><?= $day ?></div>
            <?php foreach (array_slice($dayEvents, 0, 4) as $ev):
                $color = $typeColors[$ev['type']] ?? '#94a3b8';
                $style = $statusStyles[$ev['status']] ?? '';
                $prefix = $ev['type'] === 'social' ? ($platformIcons[$ev['platform'] ?? ''] ?? '📱') . ' ' : '';
            ?>
                <a href="<?= h($ev['url']) ?>" class="cc-event" style="background:<?= $color ?>;<?= $style ?>" title="<?= h($ev['title']) ?> (<?= $ev['status'] ?>)">
                    <?= $prefix ?><?= h($ev['title']) ?>
                </a>
            <?php endforeach; ?>
            <?php if (count($dayEvents) > 4): ?>
                <div style="font-size:.65rem;color:var(--muted);text-align:center">+<?= count($dayEvents) - 4 ?> more</div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>

    <?php
    // Fill remaining cells
    $totalCells = $startWeekday + $daysInMonth;
    $remaining = (7 - ($totalCells % 7)) % 7;
    for ($i = 0; $i < $remaining; $i++):
    ?>
        <div class="cc-cell empty"></div>
    <?php endfor; ?>
</div>

<div class="cc-legend">
    <span><span class="dot" style="background:#6366f1"></span> Article</span>
    <span><span class="dot" style="background:#22c55e"></span> Page</span>
    <span><span class="dot" style="background:#f59e0b"></span> Social Post</span>
    <span><span class="dot" style="background:#ec4899"></span> 🛒 Product</span>
    <span><span class="dot" style="background:#334155;border:1px dashed #94a3b8"></span> Draft</span>
</div>

<?php
$content = ob_get_clean();
require CMS_APP . '/views/admin/layouts/topbar.php';
