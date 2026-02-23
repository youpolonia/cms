<?php
if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }
$title = $title ?? 'Social Calendar';
ob_start();

$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
$firstDayOfWeek = (int) date('N', mktime(0, 0, 0, $month, 1, $year)); // 1=Mon, 7=Sun
$monthName = date('F Y', mktime(0, 0, 0, $month, 1, $year));

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$today = (int) date('j');
$isCurrentMonth = ($month === (int) date('n') && $year === (int) date('Y'));
?>

<style>
.sc-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
.sc-nav { display: flex; align-items: center; gap: 1rem; }
.sc-nav a {
    padding: 0.4rem 0.8rem; border-radius: 6px; border: 1px solid var(--border);
    background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none;
    font-size: 0.9rem; transition: background 0.2s;
}
.sc-nav a:hover { background: var(--accent); color: #fff; }
.sc-month-title { font-size: 1.3rem; font-weight: 700; color: var(--text-primary); }

.sc-calendar {
    display: grid; grid-template-columns: repeat(7, 1fr);
    border: 1px solid var(--border); border-radius: 12px; overflow: hidden;
    background: var(--bg-secondary);
}
.sc-day-header {
    padding: 0.75rem; text-align: center; font-weight: 600; font-size: 0.8rem;
    color: var(--text-secondary); background: var(--bg-tertiary);
    text-transform: uppercase; letter-spacing: 0.5px;
    border-bottom: 1px solid var(--border);
}
.sc-day {
    min-height: 100px; padding: 0.5rem; border-right: 1px solid var(--border);
    border-bottom: 1px solid var(--border); position: relative;
    transition: background 0.2s;
}
.sc-day:nth-child(7n) { border-right: none; }
.sc-day:hover { background: var(--bg-tertiary); }
.sc-day-empty { background: var(--bg-primary); opacity: 0.5; }
.sc-day-number {
    font-size: 0.85rem; font-weight: 600; color: var(--text-secondary);
    margin-bottom: 0.3rem;
}
.sc-day.sc-today .sc-day-number {
    background: var(--accent); color: #fff; width: 24px; height: 24px;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
}

.sc-post {
    display: block; padding: 0.2rem 0.4rem; border-radius: 4px; margin-bottom: 0.2rem;
    font-size: 0.7rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    cursor: pointer; transition: opacity 0.2s; text-decoration: none;
}
.sc-post:hover { opacity: 0.8; }
.sc-post-twitter { background: rgba(29,155,240,0.2); color: #1d9bf0; }
.sc-post-linkedin { background: rgba(0,119,181,0.2); color: #0077b5; }
.sc-post-facebook { background: rgba(24,119,242,0.2); color: #1877f2; }
.sc-post-instagram { background: rgba(225,48,108,0.2); color: #e1306c; }

.sc-post-scheduled { border-left: 2px solid var(--warning); }
.sc-post-published { border-left: 2px solid var(--success); }
.sc-post-failed { border-left: 2px solid var(--danger); }
.sc-post-draft { border-left: 2px solid var(--text-muted); }

.sc-legend { display: flex; gap: 1.5rem; margin-top: 1rem; flex-wrap: wrap; }
.sc-legend-item { display: flex; align-items: center; gap: 0.4rem; font-size: 0.8rem; color: var(--text-secondary); }
.sc-legend-dot { width: 12px; height: 12px; border-radius: 3px; }

/* Post detail tooltip */
.sc-tooltip {
    display: none; position: fixed; z-index: 100;
    background: var(--bg-secondary); border: 1px solid var(--border);
    border-radius: 8px; padding: 1rem; max-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}
.sc-tooltip.active { display: block; }
.sc-tooltip-platform { font-weight: 600; margin-bottom: 0.3rem; }
.sc-tooltip-content { font-size: 0.85rem; color: var(--text-secondary); line-height: 1.4; word-break: break-word; }
.sc-tooltip-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.5rem; }

.sc-back-link {
    padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--border);
    background: var(--bg-tertiary); color: var(--text-primary); text-decoration: none;
    font-size: 0.9rem;
}
.sc-back-link:hover { background: var(--accent); color: #fff; }
</style>

<div style="max-width:1100px; margin:0 auto;">
    <div class="sc-header">
        <div style="display:flex; align-items:center; gap:1rem;">
            <h1 style="margin:0; color:var(--text-primary);">📅 Social Calendar</h1>
            <a href="/admin/social-media" class="sc-back-link">← Dashboard</a>
        </div>
        <div class="sc-nav">
            <a href="?month=<?= $prevMonth ?>&year=<?= $prevYear ?>">◀ Prev</a>
            <span class="sc-month-title"><?= h($monthName) ?></span>
            <a href="?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">Next ▶</a>
        </div>
    </div>

    <div class="sc-calendar">
        <!-- Day headers -->
        <div class="sc-day-header">Mon</div>
        <div class="sc-day-header">Tue</div>
        <div class="sc-day-header">Wed</div>
        <div class="sc-day-header">Thu</div>
        <div class="sc-day-header">Fri</div>
        <div class="sc-day-header">Sat</div>
        <div class="sc-day-header">Sun</div>

        <!-- Empty cells before first day -->
        <?php for ($i = 1; $i < $firstDayOfWeek; $i++): ?>
            <div class="sc-day sc-day-empty"></div>
        <?php endfor; ?>

        <!-- Days -->
        <?php for ($day = 1; $day <= $daysInMonth; $day++):
            $isToday = $isCurrentMonth && $day === $today;
            $dayPosts = $postsByDay[$day] ?? [];
        ?>
            <div class="sc-day <?= $isToday ? 'sc-today' : '' ?>">
                <div class="sc-day-number"><?= $day ?></div>
                <?php foreach (array_slice($dayPosts, 0, 4) as $post): ?>
                    <div class="sc-post sc-post-<?= h($post['platform']) ?> sc-post-<?= h($post['status']) ?>"
                         onclick="showTooltip(event, <?= (int)$post['id'] ?>)"
                         data-id="<?= (int)$post['id'] ?>"
                         data-platform="<?= h($post['platform']) ?>"
                         data-status="<?= h($post['status']) ?>"
                         data-content="<?= h($post['content']) ?>"
                         data-time="<?= h($post['scheduled_at'] ?? $post['published_at'] ?? $post['created_at']) ?>">
                        <?= h(ucfirst($post['platform'])) ?>: <?= h(mb_substr($post['content'], 0, 30)) ?>
                    </div>
                <?php endforeach; ?>
                <?php if (count($dayPosts) > 4): ?>
                    <div style="font-size:0.7rem; color:var(--text-muted); padding:0.2rem;">+<?= count($dayPosts) - 4 ?> more</div>
                <?php endif; ?>
            </div>
        <?php endfor; ?>

        <!-- Empty cells after last day -->
        <?php
        $totalCells = ($firstDayOfWeek - 1) + $daysInMonth;
        $remaining = (7 - ($totalCells % 7)) % 7;
        for ($i = 0; $i < $remaining; $i++): ?>
            <div class="sc-day sc-day-empty"></div>
        <?php endfor; ?>
    </div>

    <!-- Legend -->
    <div class="sc-legend">
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:rgba(29,155,240,0.5);"></div> Twitter</div>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:rgba(0,119,181,0.5);"></div> LinkedIn</div>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:rgba(24,119,242,0.5);"></div> Facebook</div>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:rgba(225,48,108,0.5);"></div> Instagram</div>
        <span style="color:var(--text-muted);">|</span>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:var(--warning); width:4px; border-radius:1px;"></div> Scheduled</div>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:var(--success); width:4px; border-radius:1px;"></div> Published</div>
        <div class="sc-legend-item"><div class="sc-legend-dot" style="background:var(--danger); width:4px; border-radius:1px;"></div> Failed</div>
    </div>
</div>

<!-- Tooltip -->
<div class="sc-tooltip" id="postTooltip">
    <div class="sc-tooltip-platform" id="ttPlatform"></div>
    <div class="sc-tooltip-content" id="ttContent"></div>
    <div class="sc-tooltip-meta" id="ttMeta"></div>
</div>

<script>
function showTooltip(e, id) {
    const el = e.currentTarget;
    const tt = document.getElementById('postTooltip');
    const platform = el.dataset.platform;
    const status = el.dataset.status;
    const content = el.dataset.content;
    const time = el.dataset.time;

    document.getElementById('ttPlatform').textContent = platform.charAt(0).toUpperCase() + platform.slice(1) + ' — ' + status;
    document.getElementById('ttContent').textContent = content;
    document.getElementById('ttMeta').textContent = time;

    tt.classList.add('active');
    tt.style.left = Math.min(e.clientX + 10, window.innerWidth - 320) + 'px';
    tt.style.top = Math.min(e.clientY + 10, window.innerHeight - 200) + 'px';

    e.stopPropagation();
}

document.addEventListener('click', function() {
    document.getElementById('postTooltip').classList.remove('active');
});
</script>

<?php
$content = ob_get_clean();
require_once CMS_APP . '/views/admin/layouts/topbar.php';
