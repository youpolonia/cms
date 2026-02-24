<?php
/**
 * Jessie Jobs — Public Browse Page
 * URL: /jobs
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-jobs/includes/class-job-listing.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$page = max(1, (int)($_GET['page'] ?? 1));
$filters = [
    'status'           => 'active',
    'job_type'         => ($_GET['type'] ?? null) ?: '',
    'remote_type'      => ($_GET['remote'] ?? null) ?: '',
    'category'         => ($_GET['category'] ?? null) ?: '',
    'location'         => ($_GET['location'] ?? null) ?: '',
    'experience_level' => ($_GET['level'] ?? null) ?: '',
    'salary_min'       => ($_GET['salary_min'] ?? null) ?: '',
    'salary_max'       => ($_GET['salary_max'] ?? null) ?: '',
    'search'           => ($_GET['q'] ?? null) ?: '',
    'sort'             => ($_GET['sort'] ?? null) ?: '',
];
$result = JobListing::getAll($filters, $page, 12);
$categories = JobListing::getCategories();
$locations = JobListing::getLocations();

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Jobs'; } catch (\Exception $e) { $siteTitle = 'Jobs'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Board — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}
        a:hover{color:var(--accent2)}

        .jb-hero{background:linear-gradient(135deg,rgba(99,102,241,.15) 0%,rgba(139,92,246,.1) 100%);border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
        .jb-hero h1{font-size:2rem;font-weight:800;margin-bottom:8px}
        .jb-hero p{color:var(--muted);font-size:1rem;margin-bottom:24px}
        .search-box{max-width:600px;margin:0 auto;display:flex;gap:8px}
        .search-box input{flex:1;background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:1rem}
        .search-box input:focus{outline:none;border-color:var(--accent)}
        .search-box button{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem}

        .jb-container{max-width:1200px;margin:0 auto;padding:24px 20px}
        .jb-layout{display:grid;grid-template-columns:260px 1fr;gap:24px}
        @media(max-width:768px){.jb-layout{grid-template-columns:1fr}}

        .jb-sidebar{position:sticky;top:24px;align-self:start}
        .sidebar-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:16px}
        .sidebar-card h3{font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .filter-select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 10px;border-radius:8px;font-size:.82rem;margin-bottom:8px}
        .filter-link{display:block;padding:6px 10px;border-radius:6px;color:var(--text);font-size:.82rem;transition:.15s}
        .filter-link:hover{background:rgba(99,102,241,.1);color:#a5b4fc}
        .filter-link.active{background:rgba(99,102,241,.15);color:#a5b4fc;font-weight:600}
        .filter-link .cnt{float:right;color:var(--muted);font-size:.72rem}

        .results-info{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px}
        .results-info .count{font-size:.85rem;color:var(--muted)}
        .sort-select{background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:8px;font-size:.82rem}

        .job-list{display:flex;flex-direction:column;gap:12px}
        .job-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:20px;transition:.2s}
        .job-card:hover{border-color:var(--accent);transform:translateY(-1px);box-shadow:0 4px 20px rgba(99,102,241,.08)}
        .job-card a{color:inherit;text-decoration:none;display:block}
        .job-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:8px}
        .job-card h3{font-size:1.05rem;font-weight:700;margin:0;line-height:1.3}
        .job-card .company{font-size:.85rem;color:var(--muted);margin-bottom:8px}
        .badge-row{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px}
        .badge{padding:3px 10px;border-radius:5px;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em}
        .badge-type{background:rgba(99,102,241,.1);color:#a5b4fc}
        .badge-remote{background:rgba(16,185,129,.12);color:#34d399}
        .badge-level{background:rgba(245,158,11,.12);color:#fbbf24}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .job-meta{display:flex;gap:16px;font-size:.78rem;color:var(--muted);flex-wrap:wrap}
        .job-meta span{display:flex;align-items:center;gap:4px}
        .salary{color:#10b981;font-weight:700}

        .jb-pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .jb-pagination a,.jb-pagination span{padding:8px 14px;border-radius:8px;font-size:.82rem;border:1px solid var(--border);color:var(--text)}
        .jb-pagination a:hover{background:rgba(99,102,241,.1);border-color:var(--accent)}
        .jb-pagination .current{background:var(--accent);color:#fff;border-color:var(--accent)}

        .empty-state{text-align:center;padding:60px 20px}
        .empty-state p{color:var(--muted)}
    </style>
</head>
<body>
    <div class="jb-hero">
        <h1>💼 Job Board</h1>
        <p>Find your next opportunity — browse jobs by role, location, and type</p>
        <form class="search-box" action="/jobs" method="get">
            <input type="text" name="q" value="<?= h($filters['search']) ?>" placeholder="Search jobs, companies, skills...">
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <div class="jb-container">
        <div class="jb-layout">
            <aside class="jb-sidebar">
                <!-- Categories -->
                <?php if (!empty($categories)): ?>
                <div class="sidebar-card">
                    <h3>📁 Categories</h3>
                    <a href="/jobs" class="filter-link <?= empty($filters['category']) ? 'active' : '' ?>">All Jobs</a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="/jobs?category=<?= urlencode($cat['category']) ?>" class="filter-link <?= ($filters['category'] ?? '') === $cat['category'] ? 'active' : '' ?>">
                        <?= h($cat['category']) ?> <span class="cnt"><?= $cat['cnt'] ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <!-- Job Type -->
                <div class="sidebar-card">
                    <h3>⏰ Job Type</h3>
                    <select class="filter-select" onchange="applyFilter('type',this.value)">
                        <option value="">All Types</option>
                        <?php foreach (['full-time'=>'Full-time','part-time'=>'Part-time','contract'=>'Contract','freelance'=>'Freelance'] as $k=>$l): ?>
                        <option value="<?= $k ?>" <?= ($filters['job_type'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Remote -->
                <div class="sidebar-card">
                    <h3>🌍 Work Mode</h3>
                    <select class="filter-select" onchange="applyFilter('remote',this.value)">
                        <option value="">All</option>
                        <?php foreach (['onsite'=>'On-site','remote'=>'Remote','hybrid'=>'Hybrid'] as $k=>$l): ?>
                        <option value="<?= $k ?>" <?= ($filters['remote_type'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Location -->
                <?php if (!empty($locations)): ?>
                <div class="sidebar-card">
                    <h3>📍 Location</h3>
                    <select class="filter-select" onchange="applyFilter('location',this.value)">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                        <option value="<?= h($loc) ?>" <?= ($filters['location'] ?? '') === $loc ? 'selected' : '' ?>><?= h($loc) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <!-- Experience Level -->
                <div class="sidebar-card">
                    <h3>📊 Experience</h3>
                    <select class="filter-select" onchange="applyFilter('level',this.value)">
                        <option value="">All Levels</option>
                        <?php foreach (['entry'=>'Entry Level','mid'=>'Mid Level','senior'=>'Senior','lead'=>'Lead / Manager'] as $k=>$l): ?>
                        <option value="<?= $k ?>" <?= ($filters['experience_level'] ?? '') === $k ? 'selected' : '' ?>><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Salary -->
                <div class="sidebar-card">
                    <h3>💰 Salary Range</h3>
                    <input type="number" class="filter-select" placeholder="Min salary" value="<?= h($filters['salary_min']) ?>" onchange="applyFilter('salary_min',this.value)">
                    <input type="number" class="filter-select" placeholder="Max salary" value="<?= h($filters['salary_max']) ?>" onchange="applyFilter('salary_max',this.value)">
                </div>
            </aside>

            <main>
                <div class="results-info">
                    <span class="count">
                        <?= $result['total'] ?> job<?= $result['total'] !== 1 ? 's' : '' ?> found
                        <?php if (!empty($filters['search'])): ?> for "<strong><?= h($filters['search']) ?></strong>"<?php endif; ?>
                    </span>
                    <select class="sort-select" onchange="applyFilter('sort',this.value)">
                        <option value="" <?= empty($filters['sort']) ? 'selected' : '' ?>>Sort: Featured</option>
                        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="salary" <?= ($filters['sort'] ?? '') === 'salary' ? 'selected' : '' ?>>Highest Salary</option>
                        <option value="views" <?= ($filters['sort'] ?? '') === 'views' ? 'selected' : '' ?>>Most Viewed</option>
                    </select>
                </div>

                <div class="job-list">
                    <?php foreach ($result['listings'] as $j): ?>
                    <div class="job-card">
                        <a href="/jobs/<?= h($j['slug']) ?>">
                            <div class="job-card-top">
                                <div>
                                    <h3><?= h($j['title']) ?></h3>
                                    <div class="company">🏢 <?= h($j['company_name'] ?: 'Company') ?></div>
                                </div>
                                <?php if ($j['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                            </div>
                            <div class="badge-row">
                                <span class="badge badge-type"><?= h($j['job_type']) ?></span>
                                <span class="badge badge-remote"><?= h($j['remote_type']) ?></span>
                                <span class="badge badge-level"><?= h($j['experience_level']) ?></span>
                                <?php if ($j['category']): ?><span class="badge badge-type"><?= h($j['category']) ?></span><?php endif; ?>
                            </div>
                            <div class="job-meta">
                                <?php if ($j['location']): ?><span>📍 <?= h($j['location']) ?></span><?php endif; ?>
                                <?php if ($j['salary_min'] || $j['salary_max']): ?>
                                <span class="salary">💰 <?= $j['salary_currency'] ?> <?= $j['salary_min']?number_format((float)$j['salary_min']):'' ?><?= ($j['salary_min']&&$j['salary_max'])?' – ':'' ?><?= $j['salary_max']?number_format((float)$j['salary_max']):'' ?></span>
                                <?php endif; ?>
                                <span>📅 <?= date('M j, Y', strtotime($j['created_at'])) ?></span>
                                <?php if ($j['expires_at']): ?><span>⏰ Expires <?= date('M j', strtotime($j['expires_at'])) ?></span><?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($result['listings'])): ?>
                <div class="empty-state">
                    <p style="font-size:1.5rem;margin-bottom:8px">🔍</p>
                    <p>No jobs found. Try a different search or filter.</p>
                </div>
                <?php endif; ?>

                <?php if ($result['pages'] > 1): ?>
                <div class="jb-pagination">
                    <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                        <?php if ($p === $page): ?>
                            <span class="current"><?= $p ?></span>
                        <?php else: ?>
                            <a href="?<?= http_build_query(array_merge(array_filter(array_diff_key($_GET, ['page' => 1])), ['page' => $p])) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>

    <script>
    function applyFilter(key, val) {
        var params = new URLSearchParams(window.location.search);
        if (val) { params.set(key, val); } else { params.delete(key); }
        params.delete('page');
        window.location.href = '/jobs?' + params.toString();
    }
    </script>
</body>
</html>
