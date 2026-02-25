<?php
/**
 * Jessie Events — Public Browse / Calendar Page
 * URL: /events
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-events/includes/class-event-manager.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$page = max(1, (int)($_GET['page'] ?? 1));
$filters = [
    'status'    => ($_GET['status'] ?? null) ?: null,
    'category'  => ($_GET['category'] ?? null) ?: null,
    'city'      => ($_GET['city'] ?? null) ?: null,
    'search'    => ($_GET['q'] ?? null) ?: null,
    'date_from' => ($_GET['date_from'] ?? null) ?: null,
    'date_to'   => ($_GET['date_to'] ?? null) ?: null,
    'month'     => ($_GET['month'] ?? null) ?: null,
    'sort'      => ($_GET['sort'] ?? null) ?: null,
];
// Default to upcoming only
if (empty($filters['status'])) $filters['status'] = 'upcoming';
$result = \EventManager::getAll(array_filter($filters), $page, 12);
$categories = \EventManager::getCategories();
$cities = \EventManager::getCities();

$settings = \EventManager::getAllSettings();
$sym = $settings['currency_symbol'] ?? '£';

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Events'; } catch (\Exception $e) { $siteTitle = 'Events'; }

// View mode
$viewMode = ($_GET['view'] ?? null) ?: 'grid';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}a:hover{color:var(--accent2)}

        .hero{background:linear-gradient(135deg,rgba(99,102,241,.15) 0%,rgba(139,92,246,.1) 100%);border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
        .hero h1{font-size:2rem;font-weight:800;margin-bottom:8px}
        .hero p{color:var(--muted);font-size:1rem;margin-bottom:24px}
        .search-box{max-width:600px;margin:0 auto;display:flex;gap:8px}
        .search-box input{flex:1;background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:1rem}
        .search-box input:focus{outline:none;border-color:var(--accent)}
        .search-box button{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem}

        .container{max-width:1200px;margin:0 auto;padding:24px 20px}
        .layout{display:grid;grid-template-columns:260px 1fr;gap:24px}
        @media(max-width:768px){.layout{grid-template-columns:1fr}}

        .sidebar{position:sticky;top:24px;align-self:start}
        .sidebar-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:16px}
        .sidebar-card h3{font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .filter-link{display:block;padding:6px 10px;border-radius:6px;color:var(--text);font-size:.85rem;transition:.15s}
        .filter-link:hover{background:rgba(99,102,241,.1);color:#a5b4fc}
        .filter-link.active{background:rgba(99,102,241,.15);color:#a5b4fc;font-weight:600}
        .filter-select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 10px;border-radius:8px;font-size:.82rem;margin-bottom:8px}

        .view-toggle{display:flex;gap:6px;margin-bottom:16px;justify-content:flex-end}
        .view-toggle a{padding:6px 12px;border-radius:6px;font-size:.8rem;background:var(--bg-card);border:1px solid var(--border);color:var(--text)}
        .view-toggle a.active{background:var(--accent);color:#fff;border-color:var(--accent)}

        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
        .event-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:.2s}
        .event-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 30px rgba(99,102,241,.1)}
        .event-card a{color:inherit;text-decoration:none;display:block;padding:20px}
        .event-card .img{width:100%;height:160px;object-fit:cover;border-radius:8px;margin-bottom:12px;background:rgba(51,65,85,.3)}
        .badge-row{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}
        .badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-free{background:rgba(16,185,129,.15);color:#34d399}
        .badge-cat{background:rgba(99,102,241,.1);color:#a5b4fc}
        .event-card h3{font-size:1rem;font-weight:700;margin-bottom:6px;line-height:1.3}
        .event-card .meta{font-size:.82rem;color:var(--muted);display:flex;flex-direction:column;gap:4px}
        .event-card .meta span{display:flex;align-items:center;gap:6px}
        .event-card .desc{font-size:.82rem;color:var(--muted);margin-top:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}

        /* Calendar */
        .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;margin-bottom:20px}
        .cal-head{text-align:center;font-size:.72rem;font-weight:700;color:var(--muted);padding:8px 0;text-transform:uppercase}
        .cal-day{background:var(--bg-card);border:1px solid var(--border);border-radius:6px;min-height:80px;padding:6px;font-size:.75rem}
        .cal-day.empty{background:transparent;border-color:transparent}
        .cal-day .dn{font-weight:700;margin-bottom:4px;color:var(--muted)}
        .cal-day.today .dn{color:var(--accent)}
        .cal-day .ev{display:block;padding:2px 4px;border-radius:3px;margin-bottom:2px;font-size:.65rem;background:rgba(99,102,241,.15);color:#a5b4fc;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-decoration:none}
        .cal-day .ev:hover{background:rgba(99,102,241,.3)}
        .cal-nav{display:flex;align-items:center;justify-content:center;gap:16px;margin-bottom:16px}
        .cal-nav a{padding:6px 14px;border-radius:6px;background:var(--bg-card);border:1px solid var(--border);color:var(--text);font-size:.85rem}
        .cal-nav span{font-size:1.1rem;font-weight:700}

        .empty-state{text-align:center;padding:60px 20px;color:var(--muted)}
        .pag{display:flex;gap:6px;margin-top:24px;justify-content:center}
        .pag a,.pag span{padding:8px 14px;border-radius:8px;font-size:.85rem;text-decoration:none}
        .pag a{background:var(--bg-card);border:1px solid var(--border);color:var(--text)}.pag a:hover{border-color:var(--accent)}
        .pag span{background:var(--accent);color:#fff}
    </style>
</head>
<body>
    <div class="hero">
        <h1>🎪 Events</h1>
        <p>Discover upcoming events, buy tickets, and have a great time</p>
        <form class="search-box" method="get" action="/events">
            <input type="text" name="q" placeholder="Search events..." value="<?= h($_GET['q'] ?? '') ?>">
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <div class="container">
        <div class="layout">
            <div class="sidebar">
                <div class="sidebar-card">
                    <h3>📅 Status</h3>
                    <a class="filter-link <?= empty($_GET['status'])||$_GET['status']==='upcoming'?'active':'' ?>" href="/events">Upcoming</a>
                    <a class="filter-link <?= ($_GET['status']??'')==='ongoing'?'active':'' ?>" href="/events?status=ongoing">Ongoing</a>
                    <a class="filter-link" href="/events?status=">All Events</a>
                </div>
                <?php if (!empty($categories)): ?>
                <div class="sidebar-card">
                    <h3>🏷️ Categories</h3>
                    <a class="filter-link <?= empty($_GET['category'])?'active':'' ?>" href="/events">All</a>
                    <?php foreach ($categories as $c): ?>
                    <a class="filter-link <?= ($_GET['category']??'')===$c?'active':'' ?>" href="/events?category=<?= urlencode($c) ?>"><?= h($c) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($cities)): ?>
                <div class="sidebar-card">
                    <h3>📍 Cities</h3>
                    <select class="filter-select" onchange="if(this.value)location.href='/events?city='+encodeURIComponent(this.value);else location.href='/events'">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?><option value="<?= h($c) ?>" <?= ($_GET['city']??'')===$c?'selected':'' ?>><?= h($c) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="sidebar-card">
                    <h3>📅 Date</h3>
                    <input type="date" class="filter-select" value="<?= h($_GET['date_from'] ?? '') ?>" onchange="location.href='/events?date_from='+this.value" placeholder="From date">
                    <input type="month" class="filter-select" value="<?= h($_GET['month'] ?? '') ?>" onchange="location.href='/events?month='+this.value" placeholder="Month">
                </div>
            </div>

            <div>
                <div class="view-toggle">
                    <a href="?view=grid<?= !empty($_GET['category'])?'&category='.urlencode($_GET['category']):'' ?>" class="<?= $viewMode==='grid'?'active':'' ?>">🔲 Grid</a>
                    <a href="?view=calendar<?= !empty($_GET['category'])?'&category='.urlencode($_GET['category']):'' ?>" class="<?= $viewMode==='calendar'?'active':'' ?>">📅 Calendar</a>
                </div>

                <?php if ($viewMode === 'calendar'):
                    $month = $_GET['month'] ?? date('Y-m');
                    $ts = strtotime($month . '-01');
                    $year = (int)date('Y', $ts);
                    $mon = (int)date('n', $ts);
                    $daysInMonth = (int)date('t', $ts);
                    $firstDay = (int)date('N', $ts); // 1=Mon
                    $prevMonth = date('Y-m', strtotime('-1 month', $ts));
                    $nextMonth = date('Y-m', strtotime('+1 month', $ts));
                    $todayStr = date('Y-m-d');
                    // Fetch events for this month
                    $calEvents = \EventManager::getAll(['month' => $month, 'status' => ''], 1, 200);
                    $eventsByDay = [];
                    foreach ($calEvents['events'] as $ce) {
                        $day = (int)date('j', strtotime($ce['start_date']));
                        $eventsByDay[$day][] = $ce;
                    }
                ?>
                <div class="cal-nav">
                    <a href="?view=calendar&month=<?= $prevMonth ?>">← Prev</a>
                    <span><?= date('F Y', $ts) ?></span>
                    <a href="?view=calendar&month=<?= $nextMonth ?>">Next →</a>
                </div>
                <div class="cal-grid">
                    <?php foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dh): ?><div class="cal-head"><?= $dh ?></div><?php endforeach; ?>
                    <?php for ($i = 1; $i < $firstDay; $i++): ?><div class="cal-day empty"></div><?php endfor; ?>
                    <?php for ($d = 1; $d <= $daysInMonth; $d++):
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $mon, $d);
                        $isToday = $dateStr === $todayStr;
                    ?>
                    <div class="cal-day <?= $isToday ? 'today' : '' ?>">
                        <div class="dn"><?= $d ?></div>
                        <?php foreach (($eventsByDay[$d] ?? []) as $ce): ?>
                        <a class="ev" href="/events/<?= h($ce['slug']) ?>" title="<?= h($ce['title']) ?>"><?= h($ce['title']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <?php endfor; ?>
                </div>
                <?php else: ?>
                <?php if (empty($result['events'])): ?>
                <div class="empty-state"><p style="font-size:1.2rem;margin-bottom:8px">No events found</p><p>Try adjusting your filters or check back later</p></div>
                <?php else: ?>
                <div class="grid">
                    <?php foreach ($result['events'] as $ev): ?>
                    <div class="event-card">
                        <a href="/events/<?= h($ev['slug']) ?>">
                            <?php if ($ev['image']): ?><img class="img" src="<?= h($ev['image']) ?>" alt="<?= h($ev['title']) ?>" loading="lazy"><?php else: ?><div class="img" style="display:flex;align-items:center;justify-content:center;font-size:2rem">🎪</div><?php endif; ?>
                            <div class="badge-row">
                                <?php if ($ev['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                                <?php if ($ev['is_free']): ?><span class="badge badge-free">🆓 Free</span><?php endif; ?>
                                <?php if ($ev['category']): ?><span class="badge badge-cat"><?= h($ev['category']) ?></span><?php endif; ?>
                            </div>
                            <h3><?= h($ev['title']) ?></h3>
                            <div class="meta">
                                <span>📅 <?= date('M j, Y', strtotime($ev['start_date'])) ?> at <?= date('H:i', strtotime($ev['start_date'])) ?></span>
                                <?php if ($ev['venue_name'] || $ev['city']): ?><span>📍 <?= h(trim(($ev['venue_name'] ?: '') . ($ev['city'] ? ', ' . $ev['city'] : ''), ', ')) ?></span><?php endif; ?>
                            </div>
                            <?php if ($ev['short_description']): ?><div class="desc"><?= h($ev['short_description']) ?></div><?php endif; ?>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php if ($result['pages'] > 1): ?>
                <div class="pag">
                    <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                        <?php if ($p == $page): ?><span><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>"><?= $p ?></a><?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
