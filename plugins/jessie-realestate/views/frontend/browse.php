<?php
/**
 * Jessie Real Estate — Public Browse Page
 * URL: /properties
 */
defined('CMS_ROOT') or define('CMS_ROOT', realpath(__DIR__ . '/../../../..'));
require_once CMS_ROOT . '/db.php';
require_once CMS_ROOT . '/plugins/jessie-realestate/includes/class-realestate-property.php';

if (!function_exists('h')) { function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); } }

$page = max(1, (int)($_GET['page'] ?? 1));
$filters = [
    'status'        => 'active',
    'property_type' => ($_GET['type'] ?? null) ?: null,
    'listing_type'  => ($_GET['listing'] ?? null) ?: null,
    'city'          => ($_GET['city'] ?? null) ?: null,
    'bedrooms_min'  => ($_GET['bedrooms'] ?? null) ?: null,
    'price_min'     => ($_GET['price_min'] ?? null) ?: null,
    'price_max'     => ($_GET['price_max'] ?? null) ?: null,
    'search'        => ($_GET['q'] ?? null) ?: null,
    'sort'          => ($_GET['sort'] ?? null) ?: null,
];
$filters = array_filter($filters, fn($v) => $v !== null);
$result = \RealEstateProperty::getAll($filters, $page, 12);
$symbol = \RealEstateProperty::getSetting('currency_symbol', '£');

// Unique cities & types for filters
$cities = db()->query("SELECT DISTINCT city FROM re_properties WHERE status = 'active' AND city != '' ORDER BY city")->fetchAll(\PDO::FETCH_COLUMN);

$siteTitle = '';
try { $stmt = db()->prepare("SELECT value FROM settings WHERE `key` = 'site_title'"); $stmt->execute(); $siteTitle = $stmt->fetchColumn() ?: 'Real Estate'; } catch (\Exception $e) { $siteTitle = 'Real Estate'; }
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Properties — <?= h($siteTitle) ?></title>
    <style>
        :root{--bg:#0f172a;--bg-card:#1e293b;--border:#334155;--text:#e2e8f0;--muted:#94a3b8;--accent:#6366f1;--accent2:#8b5cf6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:var(--bg);color:var(--text);min-height:100vh}
        a{color:var(--accent);text-decoration:none}
        a:hover{color:var(--accent2)}

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
        .filter-select{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 10px;border-radius:8px;font-size:.82rem;margin-bottom:8px}
        .filter-input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);padding:8px 10px;border-radius:8px;font-size:.82rem;margin-bottom:8px;box-sizing:border-box}
        .filter-btn{display:block;width:100%;background:linear-gradient(135deg,var(--accent),var(--accent2));color:#fff;border:none;padding:10px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;margin-top:4px}

        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px}
        .prop-card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;overflow:hidden;transition:.2s}
        .prop-card:hover{border-color:var(--accent);transform:translateY(-2px);box-shadow:0 8px 30px rgba(99,102,241,.1)}
        .prop-card a{color:inherit;text-decoration:none;display:block}
        .prop-img{height:180px;background:linear-gradient(135deg,rgba(99,102,241,.1),rgba(139,92,246,.05));display:flex;align-items:center;justify-content:center;font-size:3rem;overflow:hidden}
        .prop-img img{width:100%;height:100%;object-fit:cover}
        .prop-body{padding:16px}
        .prop-badge-row{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}
        .badge{padding:2px 8px;border-radius:4px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
        .badge-sale{background:rgba(16,185,129,.15);color:#34d399}
        .badge-rent{background:rgba(99,102,241,.15);color:#a5b4fc}
        .badge-lease{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-featured{background:rgba(245,158,11,.15);color:#fbbf24}
        .badge-type{background:rgba(99,102,241,.1);color:#a5b4fc}
        .prop-card h3{font-size:1rem;font-weight:700;margin-bottom:4px;line-height:1.3}
        .prop-price{font-size:1.2rem;font-weight:800;color:#10b981;margin-bottom:8px}
        .prop-specs{display:flex;gap:14px;font-size:.82rem;color:var(--muted);margin-bottom:8px}
        .prop-specs span{display:flex;align-items:center;gap:4px}
        .prop-loc{font-size:.78rem;color:var(--muted)}

        .results-info{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px}
        .results-info .count{font-size:.85rem;color:var(--muted)}
        .sort-select{background:var(--bg-card);border:1px solid var(--border);color:var(--text);padding:6px 12px;border-radius:8px;font-size:.82rem}

        .pagination{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .pagination a,.pagination span{padding:8px 14px;border-radius:8px;font-size:.82rem;border:1px solid var(--border);color:var(--text)}
        .pagination a:hover{background:rgba(99,102,241,.1);border-color:var(--accent)}
        .pagination .current{background:var(--accent);color:#fff;border-color:var(--accent)}
    </style>
</head>
<body>
    <div class="hero">
        <h1>🏠 Property Listings</h1>
        <p>Find your dream home, apartment, or investment property</p>
        <form class="search-box" action="/properties" method="get">
            <input type="text" name="q" value="<?= h($filters['search'] ?? '') ?>" placeholder="Search by location, property name, address...">
            <button type="submit">🔍 Search</button>
        </form>
    </div>

    <div class="container">
        <div class="layout">
            <aside class="sidebar">
                <form action="/properties" method="get" class="sidebar-card">
                    <h3>🔍 Filters</h3>
                    <select name="listing" class="filter-select">
                        <option value="">Buy / Rent</option>
                        <option value="sale" <?= ($filters['listing_type']??'')==='sale'?'selected':'' ?>>For Sale</option>
                        <option value="rent" <?= ($filters['listing_type']??'')==='rent'?'selected':'' ?>>For Rent</option>
                        <option value="lease" <?= ($filters['listing_type']??'')==='lease'?'selected':'' ?>>For Lease</option>
                    </select>
                    <select name="type" class="filter-select">
                        <option value="">All Types</option>
                        <?php foreach (['house','apartment','condo','townhouse','land','commercial'] as $t): ?>
                        <option value="<?= $t ?>" <?= ($filters['property_type']??'')===$t?'selected':'' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="city" class="filter-select">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $c): ?>
                        <option value="<?= h($c) ?>" <?= ($filters['city']??'')===$c?'selected':'' ?>><?= h($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="bedrooms" class="filter-select">
                        <option value="">Any Bedrooms</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?= $i ?>" <?= ($filters['bedrooms_min']??'')==(string)$i?'selected':'' ?>><?= $i ?>+ bedrooms</option>
                        <?php endfor; ?>
                    </select>
                    <input type="number" name="price_min" class="filter-input" placeholder="Min price" value="<?= h($filters['price_min']??'') ?>">
                    <input type="number" name="price_max" class="filter-input" placeholder="Max price" value="<?= h($filters['price_max']??'') ?>">
                    <?php if (!empty($filters['search'])): ?><input type="hidden" name="q" value="<?= h($filters['search']) ?>"><?php endif; ?>
                    <button type="submit" class="filter-btn">🔍 Apply Filters</button>
                </form>
            </aside>

            <main>
                <div class="results-info">
                    <span class="count">
                        <?= $result['total'] ?> propert<?= $result['total'] !== 1 ? 'ies' : 'y' ?> found
                        <?php if (!empty($filters['search'])): ?> for "<strong><?= h($filters['search']) ?></strong>"<?php endif; ?>
                        <?php if (!empty($filters['city'])): ?> in <strong><?= h($filters['city']) ?></strong><?php endif; ?>
                    </span>
                    <select class="sort-select" onchange="var u=new URL(location);u.searchParams.set('sort',this.value);location=u">
                        <option value="" <?= empty($filters['sort'])?'selected':'' ?>>Sort: Featured</option>
                        <option value="price_asc" <?= ($filters['sort']??'')==='price_asc'?'selected':'' ?>>Price: Low → High</option>
                        <option value="price_desc" <?= ($filters['sort']??'')==='price_desc'?'selected':'' ?>>Price: High → Low</option>
                        <option value="newest" <?= ($filters['sort']??'')==='newest'?'selected':'' ?>>Newest</option>
                        <option value="bedrooms" <?= ($filters['sort']??'')==='bedrooms'?'selected':'' ?>>Most Bedrooms</option>
                    </select>
                </div>

                <div class="grid">
                    <?php foreach ($result['properties'] as $p): ?>
                    <div class="prop-card">
                        <a href="/properties/<?= h($p['slug']) ?>">
                            <div class="prop-img">
                                <?php if (!empty($p['images'][0])): ?><img src="<?= h($p['images'][0]) ?>" alt="<?= h($p['title']) ?>"><?php else: ?>🏠<?php endif; ?>
                            </div>
                            <div class="prop-body">
                                <div class="prop-badge-row">
                                    <span class="badge badge-<?= h($p['listing_type']) ?>">For <?= h($p['listing_type']) ?></span>
                                    <span class="badge badge-type"><?= ucfirst(h($p['property_type'])) ?></span>
                                    <?php if ($p['is_featured']): ?><span class="badge badge-featured">⭐ Featured</span><?php endif; ?>
                                </div>
                                <div class="prop-price"><?= $symbol ?><?= number_format((float)$p['price']) ?><?= $p['listing_type']==='rent'?' <span style="font-size:.7rem;font-weight:400;color:var(--muted)">/month</span>':'' ?></div>
                                <h3><?= h($p['title']) ?></h3>
                                <div class="prop-specs">
                                    <?php if ($p['bedrooms'] !== null): ?><span>🛏 <?= $p['bedrooms'] ?> bed<?= $p['bedrooms']!=1?'s':'' ?></span><?php endif; ?>
                                    <?php if ($p['bathrooms'] !== null): ?><span>🚿 <?= $p['bathrooms'] ?> bath<?= $p['bathrooms']!=1?'s':'' ?></span><?php endif; ?>
                                    <?php if ($p['area_sqft']): ?><span>📐 <?= number_format($p['area_sqft']) ?> sqft</span><?php endif; ?>
                                </div>
                                <?php if ($p['city']): ?><div class="prop-loc">📍 <?= h($p['city']) ?><?= $p['state'] ? ', ' . h($p['state']) : '' ?></div><?php endif; ?>
                            </div>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>

                <?php if (empty($result['properties'])): ?>
                <div style="text-align:center;padding:60px 20px">
                    <p style="font-size:1.5rem;margin-bottom:8px">🔍</p>
                    <p style="color:var(--muted)">No properties found. Try different filters.</p>
                </div>
                <?php endif; ?>

                <?php if ($result['pages'] > 1): ?>
                <div class="pagination">
                    <?php for ($p = 1; $p <= $result['pages']; $p++): ?>
                        <?php
                        $qp = $_GET; $qp['page'] = $p;
                        $url = '/properties?' . http_build_query($qp);
                        ?>
                        <?php if ($p === $page): ?><span class="current"><?= $p ?></span><?php else: ?><a href="<?= h($url) ?>"><?= $p ?></a><?php endif; ?>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
</body>
</html>
