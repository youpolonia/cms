<?php
/**
 * Jessie LMS — Course Catalog (Public)
 * URL: /courses
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-lms/includes/class-lms-course.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$categories = \LmsCourse::getCategories();
$page = max(1, (int)($_GET['page'] ?? 1));
$filters = ['status' => 'published', 'category' => $_GET['category'] ?? '', 'search' => $_GET['q'] ?? ''];
$result = \LmsCourse::getAll($filters, $page, 12);
$courses = $result['courses'];
$totalPages = $result['pages'];

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: ''; } catch (\Exception $e) {}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Courses — <?= h($siteTitle) ?></title>
<style>
:root{--bg:#0f172a;--card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#8b5cf6;--accent2:#6366f1;--green:#22c55e;--amber:#f59e0b}
*{margin:0;padding:0;box-sizing:border-box}body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh}a{color:var(--accent);text-decoration:none}a:hover{color:var(--accent2)}

.hero{background:linear-gradient(135deg,rgba(139,92,246,.15),rgba(99,102,241,.1));border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
.hero h1{font-size:2rem;font-weight:800;margin-bottom:8px}.hero p{color:var(--muted);margin-bottom:24px}
.search{max-width:600px;margin:0 auto;display:flex;gap:8px}
.search input{flex:1;background:var(--card);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:1rem}
.search input:focus{outline:none;border-color:var(--accent)}
.search button{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer}

.container{max-width:1200px;margin:0 auto;padding:24px 20px}
.filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:24px}
.filter-btn{background:var(--card);border:1px solid var(--border);color:var(--muted);padding:6px 16px;border-radius:20px;cursor:pointer;font-size:.85rem;transition:.2s}
.filter-btn:hover,.filter-btn.active{border-color:var(--accent);color:var(--accent);background:rgba(139,92,246,.1)}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px}
.course-card{background:var(--card);border:1px solid var(--border);border-radius:16px;overflow:hidden;transition:.2s}
.course-card:hover{border-color:var(--accent);transform:translateY(-4px);box-shadow:0 12px 40px rgba(139,92,246,.15)}
.course-thumb{height:180px;background:linear-gradient(135deg,var(--accent2),var(--accent));display:flex;align-items:center;justify-content:center;font-size:3rem}
.course-body{padding:20px}
.course-body h3{font-size:1.1rem;margin-bottom:8px;line-height:1.3}
.course-body .meta{display:flex;gap:12px;font-size:.8rem;color:var(--muted);margin-bottom:12px;flex-wrap:wrap}
.course-body .desc{color:var(--muted);font-size:.875rem;line-height:1.5;margin-bottom:16px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.course-footer{display:flex;justify-content:space-between;align-items:center}
.price{font-weight:700;font-size:1.1rem;color:var(--green)}.price.paid{color:var(--accent)}
.rating{color:var(--amber);font-size:.85rem}
.badge{display:inline-block;padding:2px 8px;border-radius:6px;font-size:.7rem;font-weight:600;background:rgba(139,92,246,.15);color:var(--accent)}

.pagination{display:flex;justify-content:center;gap:8px;margin-top:32px}
.pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.875rem}
.pagination a{background:var(--card);border:1px solid var(--border);color:var(--text)}
.pagination a:hover{border-color:var(--accent)}
.pagination span.current{background:var(--accent);color:#fff}

.empty{text-align:center;padding:60px 20px;color:var(--muted)}
.back{display:inline-block;color:var(--accent);margin-bottom:16px;font-size:.875rem}
</style>
</head>
<body>
<div class="hero">
    <h1>🎓 Course Catalog</h1>
    <p>Learn new skills with our curated courses</p>
    <form class="search" method="GET" action="/courses">
        <input type="text" name="q" placeholder="Search courses..." value="<?= h($_GET['q'] ?? '') ?>">
        <button type="submit">🔍 Search</button>
    </form>
</div>

<div class="container">
    <a href="/" class="back">← Home</a>

    <div class="filters">
        <a href="/courses" class="filter-btn <?= empty($_GET['category']) ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
        <a href="/courses?category=<?= urlencode($cat) ?>" class="filter-btn <?= ($_GET['category'] ?? '') === $cat ? 'active' : '' ?>"><?= h($cat) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($courses)): ?>
    <div class="empty"><div style="font-size:3rem;margin-bottom:12px">📚</div><p>No courses found. Try a different search.</p></div>
    <?php else: ?>
    <div class="grid">
        <?php foreach ($courses as $c):
            $stars = str_repeat('⭐', max(1, (int)round((float)$c['avg_rating'])));
        ?>
        <a href="/courses/<?= h($c['slug']) ?>" style="text-decoration:none;color:inherit">
        <div class="course-card">
            <div class="course-thumb" <?= $c['thumbnail'] ? 'style="background:url('.h($c['thumbnail']).') center/cover"' : '' ?>>
                <?= !$c['thumbnail'] ? '📖' : '' ?>
            </div>
            <div class="course-body">
                <div style="display:flex;gap:6px;margin-bottom:8px">
                    <span class="badge"><?= h($c['difficulty']) ?></span>
                    <?php if ($c['category']): ?><span class="badge"><?= h($c['category']) ?></span><?php endif; ?>
                    <?php if ($c['featured']): ?><span class="badge" style="background:rgba(245,158,11,.15);color:var(--amber)">⭐ Featured</span><?php endif; ?>
                </div>
                <h3><?= h($c['title']) ?></h3>
                <div class="meta">
                    <?php if ($c['instructor_name']): ?><span>👨‍🏫 <?= h($c['instructor_name']) ?></span><?php endif; ?>
                    <?php if ($c['duration_hours'] > 0): ?><span>⏱ <?= $c['duration_hours'] ?>h</span><?php endif; ?>
                    <span>👥 <?= (int)$c['enrollment_count'] ?> students</span>
                </div>
                <div class="desc"><?= h($c['short_description'] ?: mb_substr(strip_tags($c['description']), 0, 120)) ?></div>
                <div class="course-footer">
                    <span class="price <?= $c['is_free'] ? '' : 'paid' ?>"><?= $c['is_free'] ? 'Free' : '$' . number_format((float)$c['price'], 0) ?></span>
                    <span class="rating"><?= $stars ?> (<?= (int)$c['review_count'] ?>)</span>
                </div>
            </div>
        </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php if ($page > 1): ?><a href="?page=<?= $page-1 ?>&category=<?= urlencode($_GET['category']??'') ?>&q=<?= urlencode($_GET['q']??'') ?>">← Prev</a><?php endif; ?>
        <?php for ($p = max(1,$page-2); $p <= min($totalPages,$page+2); $p++): ?>
            <?php if ($p === $page): ?><span class="current"><?= $p ?></span><?php else: ?><a href="?page=<?= $p ?>&category=<?= urlencode($_GET['category']??'') ?>&q=<?= urlencode($_GET['q']??'') ?>"><?= $p ?></a><?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?><a href="?page=<?= $page+1 ?>&category=<?= urlencode($_GET['category']??'') ?>&q=<?= urlencode($_GET['q']??'') ?>">Next →</a><?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
</body></html>
