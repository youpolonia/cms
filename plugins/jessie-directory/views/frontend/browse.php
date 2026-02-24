<?php
/**
 * Jessie Directory — Public Browse Page
 * URL: /directory
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-listing.php';
require_once CMS_ROOT . '/plugins/jessie-directory/includes/class-directory-category.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$categories = DirectoryCategory::getTree();
$page = max(1, (int)($_GET['page'] ?? 1));
$filters = [
    'status'      => 'active',
    'category_id' => $_GET['category'] ?? '',
    'city'        => $_GET['city'] ?? '',
    'search'      => $_GET['q'] ?? '',
    'sort'        => $_GET['sort'] ?? '',
    'featured'    => $_GET['featured'] ?? '',
];
$result = DirectoryListing::getAll($filters, $page, 12);

// Get unique cities for filter
$cities = db()->query("SELECT DISTINCT city FROM directory_listings WHERE status = 'active' AND city != '' ORDER BY city")->fetchAll(\PDO::FETCH_COLUMN);

// Site settings
$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Directory'; } catch (\Exception $e) { $siteTitle = 'Directory'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Directory — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}
        a:hover{color:var(--accent2)}

        /* Hero */
        .dir-hero{background:linear-gradient(135deg,rgba(99,102,241,.15) 0%,rgba(139,92,246,.1) 100%);border-bottom:1px solid var(--border);padding:48px 20px;text-align:center}
        .dir-hero h1{font-size:2rem;font-weight:800;margin-bottom:8px}
        .dir-hero p{color:var(--muted);font-size:1rem;margin-bottom:24px}
        .search-box{max-width:600px;margin:0 auto;display:flex;gap:8px}
        .search-box input{flex:1;background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:12px 16px;border-radius:10px;font-size:1rem}
        .search-box input:focus{outline:none;border-color:var(--accent)}
        .search-box button{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:12px 24px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.9rem}

        /* Layout */
        .dir-container{max-width:1200px;margin:0 auto;padding:24px 20px}
        .dir-layout{display:grid;grid-template-columns:260px 1fr;gap:24px}
        @media(max-width:768px){.dir-layout{grid-template-columns:1fr}}

        /* Sidebar */
        .dir-sidebar{position:sticky;top:24px;align-self:start}
        .sidebar-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;padding:18px;margin-bottom:16px}
        .sidebar-card h3{font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .cat-link{display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;color:var(--text);font-size:.85rem;transition:.15s}
        .cat-link:hover{background:rgba(99,102,241,.1);color:#a5b4fc}
        .cat-link.active{background:rgba(99,102,241,.15);color:#a5b4fc;font-weight:600}
        .cat-link .icon{font-size:1.1rem;width:28px;text-align:center}
        .cat-link .count{margin-left:auto;color:var(--muted);font-size:.72rem}
        .filter-select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 10px;border-radius:8px;font-size:.82rem;margin-bottom:8px}

        /* Grid */
        .dir-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px}
        .listing-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:.2s}
        .listing-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 30px rgba(99,102,241,.1)}
        .listing-card a{color:inherit;text-decoration:none;display:block;padding:20px}
        .listing-badge-row{display:flex;gap:6px;margin-bottom:10px;flex-wrap:wrap}
        .badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-verified{background:rgba(16,185,129,.15);color:#34d399}
        .badge-cat{background:rgba(99,102,241,.1);color:#a5b4fc}
        .listing-card h3{font-size:1rem;font-weight:700;margin-bottom:4px;line-height:1.3}
        .listing-card .short{font-size:.82rem;color:var(--muted);margin-bottom:10px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .listing-meta{display:flex;gap:12px;font-size:.75rem;color:var(--muted);flex-wrap:wrap}
        .listing-meta span{display:flex;align-items:center;gap:4px}
        .stars{color:#f59e0b}
        .price-range{color:#10b981;font-weight:700;letter-spacing:1px}

        /* Pagination */
        .dir-pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .dir-pagination a,.dir-pagination span{padding:8px 14px;border-radius:8px;font-size:.82rem;border:1px solid var(--border);color:var(--text)}
        .dir-pagination a:hover{background:rgba(99,102,241,.1);border-color:var(--accent)}
        .dir-pagination .current{background:var(--accent);color:#fff;border-color:var(--accent)}

        /* Submit CTA */
        .submit-cta{text-align:center;margin-top:32px;padding:24px;background:var(--bg-card);border:1px solid var(--border);border-radius:12px}
        .submit-cta h3{font-size:1.1rem;margin-bottom:8px}
        .submit-cta p{color:var(--muted);font-size:.85rem;margin-bottom:16px}
        .btn-submit{background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;padding:10px 24px;border-radius:10px;font-weight:600;font-size:.9rem;display:inline-block}

        /* Results info */
        .results-info{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px}
        .results-info .count{font-size:.85rem;color:var(--muted)}
        .sort-select{background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:8px;font-size:.82rem}
    </style>
</head>
<body>
    <div class="dir-hero">
        <h1>📍 Business Directory</h1>
        <p>Find the best local businesses, services, and professionals</p>
        <form class="search-box" action="/directory" method="get">
            <input type="text" name="q" value="<?= h($filters['search']) ?>" placeholder="Search businesses, services, locations...">
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <div class="dir-container">
        <div class="dir-layout">
            <!-- Sidebar -->
            <aside class="dir-sidebar">
                <div class="sidebar-card">
                    <h3>📁 Categories</h3>
                    <a href="/directory" class="cat-link <?= empty($filters['category_id']) ? 'active' : '' ?>">
                        <span class="icon">🏠</span> All Listings
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="/directory?category=<?= $cat['id'] ?>" class="cat-link <?= (string)($filters['category_id'] ?? '') === (string)$cat['id'] ? 'active' : '' ?>">
                        <span class="icon"><?= h($cat['icon'] ?: '📁') ?></span>
                        <?= h($cat['name']) ?>
                        <span class="count"><?= (int)$cat['listing_count'] ?></span>
                    </a>
                    <?php if (!empty($cat['children'])): ?>
                        <?php foreach ($cat['children'] as $child): ?>
                        <a href="/directory?category=<?= $child['id'] ?>" class="cat-link <?= (string)($filters['category_id'] ?? '') === (string)$child['id'] ? 'active' : '' ?>" style="padding-left:28px;font-size:.8rem">
                            <span class="icon"><?= h($child['icon'] ?: '📄') ?></span>
                            <?= h($child['name']) ?>
                            <span class="count"><?= (int)$child['listing_count'] ?></span>
                        </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($cities)): ?>
                <div class="sidebar-card">
                    <h3>📍 City</h3>
                    <select class="filter-select" onchange="location.href='/directory?city='+encodeURIComponent(this.value)">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                        <option value="<?= h($city) ?>" <?= ($filters['city'] ?? '') === $city ? 'selected' : '' ?>><?= h($city) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

                <div class="sidebar-card" style="text-align:center">
                    <a href="/directory/submit" class="btn-submit" style="display:block;font-size:.82rem;padding:8px">📝 Submit Listing</a>
                </div>
            </aside>

            <!-- Main Content -->
            <main>
                <div class="results-info">
                    <span class="count">
                        <?= $result['total'] ?> listing<?= $result['total'] !== 1 ? 's' : '' ?> found
                        <?php if (!empty($filters['search'])): ?> for "<strong><?= h($filters['search']) ?></strong>"<?php endif; ?>
                        <?php if (!empty($filters['city'])): ?> in <strong><?= h($filters['city']) ?></strong><?php endif; ?>
                    </span>
                    <select class="sort-select" onchange="location.href='?sort='+this.value+'&<?= http_build_query(array_filter(array_diff_key($filters, ['sort' => 1]))) ?>'">
                        <option value="" <?= empty($filters['sort']) ? 'selected' : '' ?>>Sort: Featured</option>
                        <option value="rating" <?= ($filters['sort'] ?? '') === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest</option>
                        <option value="views" <?= ($filters['sort'] ?? '') === 'views' ? 'selected' : '' ?>>Most Viewed</option>
                    </select>
                </div>

                <div class="dir-grid">
                    <?php foreach ($result['listings'] as $l): ?>
                    <div class="listing-card">
                        <a href="/directory/<?= h($l['slug']) ?>">
                            <div class="listing-badge-row">
                                <?php if ($l['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                                <?php if ($l['is_verified']): ?><span class="badge badge-verified">✓ Verified</span><?php endif; ?>
                                <?php if (!empty($l['category_name'])): ?><span class="badge badge-cat"><?= h($l['category_name']) ?></span><?php endif; ?>
                            </div>
                            <h3><?= h($l['title']) ?></h3>
                            <?php if ($l['short_description']): ?>
                            <div class="short"><?= h($l['short_description']) ?></div>
                            <?php endif; ?>
                            <div class="listing-meta">
                                <?php if ((float)$l['avg_rating'] > 0): ?>
                                <span><span class="stars"><?= str_repeat('★', (int)round((float)$l['avg_rating'])) ?><?= str_repeat('☆', 5 - (int)round((float)$l['avg_rating'])) ?></span> <?= number_format((float)$l['avg_rating'], 1) ?> (<?= $l['review_count'] ?>)</span>
                                <?php endif; ?>
                                <?php if ($l['city']): ?><span>📍 <?= h($l['city']) ?></span><?php endif; ?>
                                <?php if ($l['price_range']): ?><span class="price-range"><?= h($l['price_range']) ?></span><?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($result['listings'])): ?>
                <div style="text-align:center;padding:60px 20px">
                    <p style="font-size:1.5rem;margin-bottom:8px">🔍</p>
                    <p style="color:var(--muted)">No listings found. Try a different search.</p>
                </div>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($result['pages'] > 1): ?>
                <div class="dir-pagination">
                    <?php
                    $queryBase = array_filter(array_diff_key($filters, ['status' => 1]));
                    for ($p = 1; $p <= $result['pages']; $p++):
                        $queryBase['page'] = $p;
                        $url = '/directory?' . http_build_query($queryBase);
                    ?>
                        <?php if ($p === $page): ?>
                            <span class="current"><?= $p ?></span>
                        <?php else: ?>
                            <a href="<?= h($url) ?>"><?= $p ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>

                <div class="submit-cta">
                    <h3>🏢 Own a Business?</h3>
                    <p>Get listed in our directory and reach more customers.</p>
                    <a href="/directory/submit" class="btn-submit">📝 Submit Your Listing</a>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
